<?php declare(strict_types=1);

namespace AO\Client;

use function Amp\delay;

use Amp\DeferredFuture;
use AO\Internal\SendQueue;
use AO\{
	AccountUnfreezer,
	Character,
	Connection,
	Encryption,
	Exceptions\AccountFrozenException,
	Exceptions\CharacterNotFoundException,
	Exceptions\LoginException,
	Exceptions\WrongPacketOrderException,
	FrozenAccount,
	Group,
	Package,
	Package\In,
	Package\Out,
	Parser,
	SendPriority,
	Utils
};
use Closure;
use Exception;
use Nadylib\LeakyBucket\LeakyBucket;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;

use Throwable;

class SingleClient {
	public const UID_NONE = 0xFF_FF_FF_FF;

	/** @var array<int,string> */
	private array $uidToName = [];

	/** @var array<string,int> */
	private array $nameToUid = [];

	/** @var array<string, EventLoop\Suspension<?int>[]> */
	private array $pendingUidLookups = [];

	/** @var array<int,bool> */
	private array $buddylist = [];

	/** @var array<string,Group> */
	private array $publicGroups = [];

	private float $lastPackage = 0;
	private float $lastPong = 0;

	private bool $readyOnNextPong = false;

	/**
	 * True when the bot has finished receiving the initial
	 * buddylist updates which don't correspond to state changes.
	 */
	private bool $isReady = false;

	/**
	 * A list of callbacks to call when the ready status flips
	 * from false to true
	 *
	 * @var Closure[]
	 */
	private array $readyListeners = [];

	private readonly LeakyBucket $bucket;

	private SendQueue $sendQueue;

	private ?string $loggedInChar = null;

	private ?int $loggedInUid = null;

	public function __construct(
		private Connection $connection,
		private Parser $parser,
		private ?LoggerInterface $logger=null,
		?LeakyBucket $bucket=null,
		private ?AccountUnfreezer $accountUnfreezer=null,
		?SendQueue $sendQueue=null
	) {
		$this->sendQueue = $sendQueue ?? new SendQueue();
		$this->bucket = $bucket ?? new LeakyBucket(size: 5, refillDelay: 1.0);
	}

	public function getStatistics(): Statistics {
		return $this->connection->getStatistics();
	}

	public function isReady(): bool {
		return $this->isReady;
	}

	/** Get the UNIX timestamp when the last package was received */
	public function getLastPackageReceived(): float {
		return $this->lastPackage;
	}

	/** Get the UNIX timestamp when the last ping package was received */
	public function getLastPongSent(): float {
		return $this->lastPong;
	}

	/**
	 * Request a callback when the connection is ready to
	 * process packages and has finished receiving the
	 * initial buddylist updates
	 *
	 * @phpstan-param Closure():mixed $callback
	 */
	public function onReady(Closure $callback): void {
		$this->readyListeners []= $callback;
	}

	/**
	 * Get a list of all public group we're in
	 *
	 * @return array<string,Group> The groups indexed by their name
	 */
	public function getGroups(): array {
		return $this->publicGroups;
	}

	/**
	 * Get information about a public group we're in
	 *
	 * @param string|Group\GroupId $id the name or id of the group
	 *
	 * @return Group|null Information about the group, or NULL, if we're not in it
	 */
	public function getGroup(string|Group\GroupId $id): ?Group {
		if (is_string($id)) {
			return $this->publicGroups[$id] ?? null;
		}
		foreach ($this->publicGroups as $name => $group) {
			if ($group->id->sameAs($id)) {
				return $group;
			}
		}
		return null;
	}

	/**
	 * Look up the UID of a given character
	 *
	 * @param string $character The name of the character
	 * @param bool   $cacheOnly If false, then don't send lookup-packages, only use the cache
	 *
	 * @return int|null Either the UID, or NULL, if the character doesn't exist/is frozen
	 */
	public function lookupUid(string $character, bool $cacheOnly=false): ?int {
		$character = Utils::normalizeCharacter($character);
		$uid = $this->nameToUid[$character] ?? null;
		if (isset($uid) || $cacheOnly) {
			return ($uid === self::UID_NONE) ? null : $uid;
		}
		$this->pendingUidLookups[$character] ??= [];
		$suspension = EventLoop::getSuspension();
		$this->write(new Out\CharacterLookup(name: $character));
		$this->pendingUidLookups[$character] []= $suspension;
		return $suspension->suspend();
	}

	/**
	 * Look up the character name of a given UID
	 *
	 * @param int  $uid       The user ID to look up
	 * @param bool $cacheOnly If false, then don't send lookup-packages,
	 *                        only use the cache
	 *
	 * @return string|null Either the name of the character, or NULL, if the UID is currently not in use
	 */
	public function lookupCharacter(int $uid, bool $cacheOnly=false): ?string {
		$character = $this->uidToName[$uid] ?? null;
		if (isset($character) || $cacheOnly) {
			return $character;
		}
		$this->write(new Out\BuddyAdd(charId: $uid));
		unset($this->nameToUid['X']);
		$this->lookupUid('X');
		$this->write(new Out\BuddyRemove(charId: $uid));
		return $this->uidToName[$uid] ?? null;
	}

	/**
	 * Get the buddylist as uid => online
	 *
	 * @return array<int,bool>
	 */
	public function getBuddylist(): array {
		return $this->buddylist;
	}

	/**
	 * Get the full name => uid cache
	 *
	 * @return array<string,int>
	 */
	public function getUidCache(): array {
		return $this->nameToUid;
	}

	/**
	 * Get the full uid => name cache
	 *
	 * @return array<int,string>
	 */
	public function getNameCache(): array {
		return $this->uidToName;
	}

	/**
	 * Check if a given UID is currently online
	 *
	 * @param int  $uid       The character uid to check
	 * @param bool $cacheOnly If true, only check our cached online-status
	 *                        from the buddylist, otherwise send a packet
	 *
	 * @return bool|null Either true/false if online/offline or null, if the status is unknown
	 */
	public function isOnline(int $uid, bool $cacheOnly=true): ?bool {
		if ($uid === $this->loggedInUid) {
			return true;
		}
		$cachedOnlineStatus = $this->buddylist[$uid] ?? null;
		if ($cacheOnly || $cachedOnlineStatus !== null) {
			return $cachedOnlineStatus;
		}
		$this->write(new Out\BuddyAdd(charId: $uid));
		$dummyName = substr(base64_encode(random_bytes(12)), 0, 12);
		unset($this->nameToUid[$dummyName]);
		$this->lookupUid($dummyName);
		unset($this->nameToUid[$dummyName]);
		$onlineStatus = $this->buddylist[$uid] ?? null;
		$this->write(new Out\BuddyRemove(charId: $uid));
		return $onlineStatus;
	}

	/**
	 * Read the next package, waiting for it
	 *
	 * @return Package\InPackage|null Either null, if the connection was closed, or
	 *                                the next package that was read
	 */
	public function read(): ?Package\InPackage {
		$binPackage = $this->connection->read();
		if ($binPackage === null) {
			$this->logger?->info('Stream has closed the connection');
			return null;
		}
		$this->lastPackage = microtime(true);
		$package = $this->parser->parseBinaryPackage($binPackage);
		$this->handleIncomingPackage($package);
		return $package;
	}

	public function write(Package\OutPackage $package, SendPriority $priority=SendPriority::Medium): void {
		$this->logger?->debug('Sending package {package}', [
			'package' => $package,
		]);
		$binPackage = $package->toBinaryPackage();
		if ($package instanceof Package\Out\RateLimited) {
			$this->logger?->debug('Sending rate-limited package via bucket-queue');
			$suspension = new DeferredFuture();
			$future = $suspension->getFuture();
			$this->sendQueue->push($package, $priority, $suspension);
			$this->bucket->take(callback: $this->sendNextQueueItem(...));
			$future->await();
		} else {
			$this->logger?->debug('Sending non-rate-limited package instantly');
			$this->connection->write($binPackage->toBinary());
		}
		if ($package instanceof Package\Out\Pong) {
			$this->lastPong = microtime(true);
		}
	}

	public function getQueueSize(): int {
		return $this->sendQueue->getSize();
	}

	public function disconnect(): void {
		$this->connection->end();
	}

	public function login(string $username, string $password, string $character): void {
		$this->loggedInChar = null;
		$this->loggedInUid = null;
		$this->logger?->debug("Logging in with username {$username}", ['username' => $username]);
		$this->publicGroups = [];
		$this->buddylist = [];
		$character = Utils::normalizeCharacter($character);
		$loginSeed = $this->read();
		if ($loginSeed === null) {
			throw new LoginException('No login seed received');
		}
		if (!($loginSeed instanceof In\LoginSeed)) {
			throw new WrongPacketOrderException(
				'Expected ' . In\LoginSeed::class . ', got ' . $loginSeed::class
			);
		}
		$this->logger?->debug('Received login seed {seed}, calculating reply', ['seed' => $loginSeed->serverSeed]);
		$key = Encryption\TEA::generateLoginKey(
			serverKey: $loginSeed->serverSeed,
			username: $username,
			password: $password,
		);
		$this->write(new Out\LoginRequest(
			username: $username,
			key: $key,
		));
		$response = $this->read();
		if ($response === null) {
			throw new LoginException('Connection unexpectedly closed');
		}
		if ($response instanceof In\LoginError) {
			$errorMsgs = explode('|', $response->error);
			if (count($errorMsgs) === 3 && $errorMsgs[2] === '/Account system denies login') {
				if (isset($this->accountUnfreezer) && $this->accountUnfreezer->unfreeze()) {
					$this->logger?->notice('Account {account} successfully unfrozen, waiting {delay}s', [
						'account' => $username,
						'delay' => 5,
					]);
					$this->accountUnfreezer = null;
					delay(5);
					$this->login(...func_get_args());
					return;
				}
				$parts = explode(': ', $errorMsgs[0] ?? '');
				throw new AccountFrozenException(
					account: new FrozenAccount(
						username: $username,
						subscriptionId: isset($parts[1]) ? (int)$parts[1] : null,
					),
				);
			}
			$this->logger?->error('Error from login server: {error}', [
				'error' => $response->error,
			]);
			throw new LoginException($response->error);
		}
		if (!($response instanceof In\LoginCharlist)) {
			throw new WrongPacketOrderException(
				'Expected ' . In\LoginCharlist::class . ', got ' . $response::class
			);
		}
		$uid = $this->getUidFromCharlist($response, $character);
		if ($uid === null) {
			$this->logger?->error(
				'The character {charName} is not on the account {account}. Found only {validNames}',
				[
					'account' => $username,
					'validNames' => implode(', ', $response->characters),
					'charName' => $character,
				]
			);
			throw new CharacterNotFoundException(
				"The character {$character} is not on account {$username}"
			);
		}
		$this->write(new Out\LoginSelectCharacter(charId: $uid));
		$response = $this->read();
		if ($response === null) {
			throw new LoginException('Connection unexpectedly closed');
		}
		if (!($response instanceof In\LoginOk)) {
			throw new WrongPacketOrderException(
				'Expected ' . In\LoginOk::class . ', got ' . $response::class
			);
		}
		$this->loggedInChar = $character;
		$this->loggedInUid = $uid;
	}

	/**
	 * Get a list of all characters on the given account
	 *
	 * @param string $username Username to login with
	 * @param string $password Password to login with
	 *
	 * @return list<Character> The characters on this account
	 */
	public function getChars(string $username, string $password): array {
		if (isset($this->loggedInUid) || isset($this->loggedInChar)) {
			throw new Exception('Cannot get list of characters with an already logged in connection');
		}
		$this->loggedInChar = null;
		$this->loggedInUid = null;
		$this->logger?->debug("Logging in with username {$username}", ['username' => $username]);
		$this->publicGroups = [];
		$this->buddylist = [];
		$loginSeed = $this->read();
		if ($loginSeed === null) {
			throw new LoginException('No login seed received');
		}
		if (!($loginSeed instanceof In\LoginSeed)) {
			throw new WrongPacketOrderException(
				'Expected ' . In\LoginSeed::class . ', got ' . $loginSeed::class
			);
		}
		$this->logger?->debug('Received login seed {seed}, calculating reply', ['seed' => $loginSeed->serverSeed]);
		$key = Encryption\TEA::generateLoginKey(
			serverKey: $loginSeed->serverSeed,
			username: $username,
			password: $password,
		);
		$this->write(new Out\LoginRequest(
			username: $username,
			key: $key,
		));
		$response = $this->read();
		if ($response === null) {
			throw new LoginException('Connection unexpectedly closed');
		}
		if ($response instanceof In\LoginError) {
			$errorMsgs = explode('|', $response->error);
			if (count($errorMsgs) === 3 && $errorMsgs[2] === '/Account system denies login') {
				if (isset($this->accountUnfreezer) && $this->accountUnfreezer->unfreeze()) {
					$this->logger?->notice('Account {account} successfully unfrozen, waiting {delay}s', [
						'account' => $username,
						'delay' => 5,
					]);
					$this->accountUnfreezer = null;
					delay(5);
					return $this->getChars(...func_get_args());
				}
				$parts = explode(': ', $errorMsgs[0] ?? '');
				throw new AccountFrozenException(
					account: new FrozenAccount(
						username: $username,
						subscriptionId: isset($parts[1]) ? (int)$parts[1] : null,
					),
				);
			}
			$this->logger?->error('Error from login server: {error}', [
				'error' => $response->error,
			]);
			throw new LoginException($response->error);
		}
		if (!($response instanceof In\LoginCharlist)) {
			throw new WrongPacketOrderException(
				'Expected ' . In\LoginCharlist::class . ', got ' . $response::class
			);
		}
		$result = [];
		for ($i = 0; $i < count($response->characters); $i++) {
			$result []= new Character(
				uid: $response->charIds[$i],
				name: $response->characters[$i],
				level: $response->levels[$i],
				online: $response->online[$i],
			);
		}
		$this->disconnect();
		return $result;
	}

	/**
	 * Some packages trigger internal behaviour,
	 * like adding buddies to the buddylist, or tracking
	 * public groups the bot is in. This happens here.
	 */
	protected function handleIncomingPackage(Package\InPackage $package): void {
		if (!($package instanceof In\Ping)) {
			$this->readyOnNextPong = false;
		}
		if ($package instanceof In\CharacterLookupResult) {
			$this->handleCharacterLookupResult($package);
		} elseif ($package instanceof In\CharacterName) {
			$this->handleCharacterName($package);
		} elseif ($package instanceof In\BuddyState) {
			$this->handleBuddyState($package);
		} elseif ($package instanceof In\BuddyRemoved) {
			$this->handleBuddyRemoved($package);
		} elseif ($package instanceof In\GroupJoined) {
			$this->handleGroupJoined($package);
		} elseif ($package instanceof In\Ping) {
			$this->handlePing($package);
		} elseif ($package instanceof In\GroupLeft) {
			$this->handleGroupLeft($package);
		}
		if ($this->lastPong < 1 && isset($this->loggedInUid)) {
			$this->readyOnNextPong = true;
			$this->write(new Out\Pong(extra: 'ready'));
		}
	}

	protected function handleCharacterLookupResult(In\CharacterLookupResult $package): void {
		$this->logger?->debug('In\\ClientLookup received, caching uid <=> name lookups');
		$this->nameToUid[$package->name] = $package->charId;
		$this->uidToName[$package->charId] = $package->name;
		$suspended = $this->pendingUidLookups[$package->name] ?? [];
		unset($this->pendingUidLookups[$package->name]);
		$this->logger?->debug('{num_waiting} clients waiting for lookup result', [
			'num_waiting' => count($suspended),
		]);
		$numFiber = 1;
		foreach ($suspended as $thread) {
			$this->logger?->debug('Resuming fiber #{fiber}', ['fiber' => $numFiber++]);
			$thread->resume($package->getUid());
		}
	}

	protected function handleCharacterName(In\CharacterName $package): void {
		$this->logger?->debug('In\\ClientName received, caching {uid} <=> "{name}" lookups', [
			'uid' => $package->getUid(),
			'name' => $package->name,
		]);
		$this->nameToUid[$package->name] = $package->charId;
		$this->uidToName[$package->charId] = $package->name;
	}

	protected function handleBuddyState(In\BuddyState $package): void {
		$this->logger?->debug('In\\BuddyState received, putting into buddylist with status "{online}"', [
			'online' => ($package->online ? 'online' : 'offline'),
		]);
		$this->buddylist[$package->charId] = $package->online;
	}

	protected function handleBuddyRemoved(In\BuddyRemoved $package): void {
		$this->logger?->debug('In\\BuddyRemoved received, removing from buddylist');
		unset($this->buddylist[$package->charId]);
	}

	protected function handleGroupJoined(In\GroupJoined $package): void {
		$group = new Group(
			id: $package->groupId,
			name: $package->groupName,
			flags: $package->flags
		);
		$this->logger?->debug('New group {group} announced', [
			'group' => $group,
		]);
		$this->publicGroups[$package->groupName] = $group;
	}

	protected function handlePing(In\Ping $package): void {
		if ($this->isReady || $package->extra !== 'ready') {
			return;
		}
		if (isset($this->loggedInUid) && $this->readyOnNextPong) {
			EventLoop::defer($this->triggerOnReady(...));
		} else {
			$this->readyOnNextPong = true;
			$this->write(new Out\Pong(extra: 'ready'));
		}
	}

	protected function handleGroupLeft(In\GroupLeft $package): void {
		foreach ($this->publicGroups as $name => $group) {
			if ($package->groupId->sameAs($group->id)) {
				$this->logger?->debug('Removing the group {group} from our list', [
					'group' => $name,
				]);
				unset($this->publicGroups[$name]);
			}
		}
	}

	protected function triggerOnReady(): void {
		if ($this->isReady) {
			return;
		}
		$this->isReady = true;
		$this->logger?->notice('{charName} is now ready', [
			'charName' => $this->loggedInChar,
		]);
		while (null !== ($callback = array_shift($this->readyListeners))) {
			$this->logger?->debug('Calling {closure}', [
				'closure' => Utils::closureToString($callback),
			]);
			try {
				$callback();
			} catch (Throwable $e) {
				$this->logger?->error('Error calling {closure}: {error}', [
					'closure' => Utils::closureToString($callback),
					'error' => $e->getMessage(),
					'exception' => $e,
				]);
			}
		}
	}

	private function sendNextQueueItem(): void {
		$item = $this->sendQueue->shift();
		if (!isset($item)) {
			return;
		}
		$this->connection->write($item->package->toBinaryPackage()->toBinary());
		if (!$item->future->isComplete()) {
			$item->future->complete();
		}
	}

	/** Get the UID of the bot character from the charlist package */
	private function getUidFromCharlist(In\LoginCharlist $charlist, string $character): ?int {
		$character = Utils::normalizeCharacter($character);
		$uid = null;
		for ($i = 0; $i < count($charlist->characters); $i++) {
			$this->uidToName[$charlist->charIds[$i]] = $charlist->characters[$i];
			$this->nameToUid[$charlist->characters[$i]] = $charlist->charIds[$i];
			if ($charlist->characters[$i] === $character) {
				$uid = $charlist->charIds[$i];
			}
		}
		return $uid;
	}
}
