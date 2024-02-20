<?php declare(strict_types=1);

namespace AO;

use AO\Package\{In, Out};
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;

class BasicClient {
	public const UID_NONE = 0xFFFFFFFF;

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

	public function __construct(
		private LoggerInterface $logger,
		private Connection $connection,
		private Parser $parser,
	) {
	}

	/**
	 * Get infomation about a public group we're in
	 *
	 * @param string|Group\Id $id the name or id of the group
	 *
	 * @return Group|null Information about the group, or NULL, if we're not in it
	 */
	public function getGroup(string|Group\Id $id): ?Group {
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
	 * @param bool   $cacheOnly If false, then don't send lookup-packages, only use the cacne
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
	 * Look up the chacter name of a given UID
	 *
	 * @param int  $uid       The user ID to look up
	 * @param bool $cacheOnly If false, then don't send lookup-packages,
	 *                        only use the cacne
	 *
	 * @return string|null Either the name of the character, or NULL, if the UID is currently not in use
	 */
	public function lookupCharacter(int $uid, bool $cacheOnly=false): ?string {
		$character = $this->uidToName[$uid] ?? null;
		if (isset($character) || $cacheOnly) {
			return $character;
		}
		$this->write(new Out\BuddyAdd(charId: $uid));
		unset($this->nameToUid["X"]);
		$this->lookupUid("X");
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
		$cachedOnlineStatus = $this->buddylist[$uid] ?? null;
		if ($cacheOnly || $cachedOnlineStatus !== null) {
			return $cachedOnlineStatus;
		}
		$this->write(new Out\BuddyAdd(charId: $uid));
		unset($this->nameToUid["X"]);
		$this->lookupUid("X");
		$onlineStatus = $this->buddylist[$uid] ?? null;
		$this->write(new Out\BuddyRemove(charId: $uid));
		return $onlineStatus;
	}

	/**
	 * Read the next package, waiting for it
	 *
	 * @return Package\In|null Either null, if the connection was closed, or
	 *                         the next package that was read
	 */
	public function read(): ?Package\In {
		$binPackage = $this->connection->read();
		if ($binPackage === null) {
			$this->logger->info("Stream has closed the connection");
			return null;
		}
		$package = $this->parser->parseBinaryPackage($binPackage);
		$this->handleIncomingPackage($package);
		return $package;
	}

	public function write(Package\Out $package): void {
		$binPackage = $package->toBinary();
		$this->connection->write($binPackage->toBinary());
	}

	public function disconnect(): void {
		$this->connection->end();
	}

	public function login(string $username, string $password, string $character): void {
		$this->logger->debug("Logging in with username {$username}", ["username" => $username]);
		$this->publicGroups = [];
		$this->buddylist = [];
		$character = Utils::normalizeCharacter($character);
		$loginSeed = $this->read();
		if ($loginSeed === null) {
			throw new LoginException("No login seed received");
		}
		if (!($loginSeed instanceof In\LoginSeed)) {
			throw new WrongPacketOrderException(
				"Expected " . In\LoginSeed::class . ", got " . get_class($loginSeed)
			);
		}
		$this->logger->debug("Received login seed {seed}, calculating reply", ["seed" => $loginSeed->serverSeed]);
		$key = TEA\TEA::generateLoginKey(
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
			throw new LoginException("Connection unexpectedly closed");
		}
		if ($response instanceof In\LoginError) {
			$errorMsgs = explode("|", $response->error);
			if (count($errorMsgs) === 3 && $errorMsgs[2] === "/Account system denies login") {
				$parts = explode(": ", $errorMsgs[0] ?? "");
				throw new AccountFrozenException($parts[1] ?? "");
			}
			$this->logger->error("Error from login server: {error}", [
				"error" => $response->error,
			]);
			throw new LoginException($response->error);
		}
		if (!($response instanceof In\LoginCharlist)) {
			throw new WrongPacketOrderException(
				"Expected " . In\LoginCharlist::class . ", got " . get_class($response)
			);
		}
		$uid = $this->getUidFromCharlist($response, $character);
		if ($uid === null) {
			$this->logger->error(
				"The character {charName} is not on the account {account}. Found only {validNames}",
				[
					"account" => $username,
					"validNames" => join(", ", $response->characters),
					"charName" => $character,
				]
			);
			throw new CharacterNotFoundException(
				"The character {$character} is not on account {$username}"
			);
		}
		$this->write(new Out\LoginSelectCharacter(charId: $uid));
		$response = $this->read();
		if ($response === null) {
			throw new LoginException("Connection unexpectedly closed");
		}
		if (!($response instanceof In\LoginOk)) {
			throw new WrongPacketOrderException(
				"Expected " . In\LoginOk::class . ", got " . get_class($response)
			);
		}
	}

	/**
	 * Some packages trigger internal behaviour,
	 * like adding buddys to the buddylist, or tracking
	 * public groups the bot is in. This happens here.
	 *
	 * @internal description
	 */
	protected function handleIncomingPackage(Package\In $package): void {
		if ($package instanceof In\CharacterLookupResult) {
			$this->logger->debug("In\\ClientLookup received, caching uid <=> name lookups");
			$this->nameToUid[$package->name] = $package->charId;
			$this->uidToName[$package->charId] = $package->name;
			$suspended = $this->pendingUidLookups[$package->name] ?? [];
			unset($this->pendingUidLookups[$package->name]);
			$this->logger->debug("{num_waiting} clients waiting for lookup result", [
				"num_waiting" => count($suspended),
			]);
			$numFiber = 1;
			foreach ($suspended as $thread) {
				$this->logger->debug("Resuming fiber #{fiber}", ["fiber" => $numFiber++]);
				$thread->resume($package->getUid());
			}
		} elseif ($package instanceof In\CharacterName) {
			$this->logger->debug("In\\ClientName received, caching {uid} <=> \"{name}\" lookups", [
				"uid" => $package->getUid(),
				"name" => $package->name,
			]);
			$this->nameToUid[$package->name] = $package->charId;
			$this->uidToName[$package->charId] = $package->name;
		} elseif ($package instanceof In\BuddyAdded) {
			$this->logger->debug("In\\BuddyAdded received, putting into buddylist with status \"{online}\"", [
				"online" => ($package->online ? "online" : "offline"),
			]);
			$this->buddylist[$package->charId] = $package->online;
		} elseif ($package instanceof In\BuddyRemoved) {
			$this->logger->debug("In\\BuddyRemoved received, removing from buddylist");
			unset($this->buddylist[$package->charId]);
		} elseif ($package instanceof In\GroupJoined) {
			$group = new Group(
				id: $package->groupId,
				name: $package->groupName,
				flags: $package->flags
			);
			$this->logger->debug("New group {group} announced", [
				"group" => $group,
			]);
			$this->publicGroups[$package->groupName] = $group;
		} elseif ($package instanceof In\GroupLeft) {
			foreach ($this->publicGroups as $name => $group) {
				if ($package->groupId->sameAs($group->id)) {
					$this->logger->debug("Removing the group {group} from our list", [
						"group" => $name,
					]);
					unset($this->publicGroups[$name]);
				}
			}
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
