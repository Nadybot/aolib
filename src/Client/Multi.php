<?php declare(strict_types=1);

namespace AO\Client;

use function Amp\Future\awaitAll;
use function Amp\Socket\connect;
use function Amp\{async, delay};

use Amp\Pipeline\{ConcurrentIterator, Queue};
use AO\{Group, Package, Parser, Utils};
use Closure;
use InvalidArgumentException;
use Nadylib\LeakyBucket\LeakyBucket;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;
use Throwable;

class Multi {
	/** @var array<string,Basic> */
	private array $connections = [];

	/** @var WorkerConfig[] */
	private array $configs = [];

	/** @var Queue<WorkerPackage> */
	private readonly Queue $readQueue;

	/**
	 * A list of callbacks to call when the ready status flips
	 * from false to true
	 *
	 * @var Closure[]
	 */
	private array $readyListeners = [];

	/**
	 * True when the bot has finished receiving the initial
	 * buddylist updates which don't correspond to state changes.
	 */
	private bool $isReady = false;

	/**
	 * @param WorkerConfig[] $workers
	 *
	 * @phpstan-param non-empty-list<WorkerConfig> $workers
	 */
	public function __construct(
		array $workers,
		public readonly ?string $mainCharacter=null,
		private ?LoggerInterface $logger=null,
		private ?Parser $parser=null,
		private ?LeakyBucket $bucket=null,
	) {
		$this->readQueue = new Queue(0);
		// @phpstan-ignore-next-line
		if (empty($workers)) {
			throw new InvalidArgumentException(__CLASS__ . "::" . __FUNCTION__ . "(\$workers\) must me non-empty");
		}
		foreach ($workers as $key => $workerConfig) {
			if (!($workerConfig instanceof WorkerConfig)) {
				throw new InvalidArgumentException(__CLASS__ . "::" . __FUNCTION__ . "(\$workers[\$key\]\) is not a WorkerConfig");
			}
			$this->configs []= $workerConfig;
		}
	}

	/**
	 * Get infomation about a public group we're in
	 *
	 * @param string|Group\Id $id the name or id of the group
	 *
	 * @return Group|null Information about the group, or NULL, if we're not in it
	 */
	public function getGroup(string|Group\Id $id): ?Group {
		return $this->getBestWorker()?->getGroup($id);
	}

	public function isReady(): bool {
		return $this->isReady;
	}

	public function disconnect(): void {
		foreach ($this->connections as $connection) {
			$connection->disconnect();
		}
	}

	/**
	 * Get the UNIX timestamp when the last package was received by each worker
	 *
	 * @return array<string,float>
	 */
	public function getLastPackageReceived(): array {
		$result = [];
		foreach ($this->connections as $name => $connection) {
			$result[$name] = $connection->getLastPackageReceived();
		}
		return $result;
	}

	/**
	 * Get the UNIX timestamp when the last pong package was sent by each worker
	 *
	 * @return array<string,float>
	 */
	public function getLastPongSent(): array {
		$result = [];
		foreach ($this->connections as $name => $connection) {
			$result[$name] = $connection->getLastPongSent();
		}
		return $result;
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

	public function getStatistics(): Statistics {
		return array_reduce(
			$this->connections,
			function (Statistics $stats, Basic $client): Statistics {
				return $stats->add($client->getStatistics());
			},
			new Statistics()
		);
	}

	public function login(): void {
		$this->isReady = false;
		$futures = [];
		foreach ($this->configs as $config) {
			$futures []= async($this->clientLogin(...), $config);
		}

		/** @var array{0:\Throwable[],1:WorkerThread[]} */
		$workers = awaitAll($futures);
		foreach ($workers[0] as $exception) {
			throw $exception;
		}
		$this->logger?->notice("All workers logged in successfully.");
		$numReady = 0;
		foreach ($workers[1] as $worker) {
			$this->connections[$worker->config->character] = $worker->client;
			async($this->workerLoop(...), $worker);
			$worker->client->onReady(function () use (&$numReady): void {
				$numReady++;
				if ($numReady >= count($this->configs)) {
					$this->triggerOnReady();
				}
			});
		}
		EventLoop::onSignal(SIGINT, function (string $cancellation) {
			foreach ($this->connections as $id => $connection) {
				$connection->disconnect();
			}
			EventLoop::cancel($cancellation);
		});
	}

	public function write(Package\Out $package, ?string $worker=null): void {
		$this->getBestWorker()?->write($package);
	}

	/** @return ConcurrentIterator<WorkerPackage> */
	public function getPackages(): ConcurrentIterator {
		return $this->readQueue->iterate();
	}

	/**
	 * Look up the UID of a given character
	 *
	 * @param string $character The name of the character
	 * @param bool   $cacheOnly If false, then don't send lookup-packages, only use the cacne
	 *
	 * @return int|null Either the UID, or NULL, if the character doesn't exist/is frozen
	 */
	public function lookupUid(string $character, bool $cacheOnly=false, ?string $worker=null): ?int {
		$uid = null;
		if (!isset($worker)) {
			foreach ($this->connections as $id => $client) {
				$uid ??= $client->lookupUid($character, true);
			}
		}
		return $uid ?? $this->getBestWorker($worker)?->lookupUid($character, $cacheOnly);
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
	public function lookupCharacter(int $uid, bool $cacheOnly=false, ?string $worker=null): ?string {
		$character = null;
		if (!isset($worker)) {
			foreach ($this->connections as $id => $client) {
				$character ??= $client->lookupCharacter($uid, true);
			}
		}
		return $character ?? $this->getBestWorker($worker)?->lookupCharacter($uid, $cacheOnly);
	}

	/** Get the character of the worker with the least buddies on its list */
	public function getMostEmptyWorker(): string {
		$fill = $this->getBuddylistSize();
		asort($fill);
		return array_keys($fill)[0];
	}

	/**
	 * Get the current amount of used up buddylist slots per worker
	 *
	 * @return array<string,int>
	 */
	public function getBuddylistSize(): array {
		$result = [];
		foreach ($this->connections as $id => $client) {
			$result[$id] = count($client->getBuddylist());
		}
		return $result;
	}

	/** Add a buddy to the most empty worker */
	public function buddyAdd(int $uid): void {
		$this->write(
			package: new Package\Out\BuddyAdd(charId: $uid),
			worker: $this->getMostEmptyWorker(),
		);
	}

	/** Remove a buddy from all workers */
	public function buddyRemove(int $uid): void {
		foreach ($this->connections as $id => $connection) {
			$buddies = $connection->getBuddylist();
			if (isset($buddies[$uid])) {
				$connection->write(new Package\Out\BuddyRemove($uid));
			}
		}
	}

	/**
	 * Get the buddylist as uid => online
	 *
	 * @return array<int,bool>
	 */
	public function getBuddylist(?string $worker=null): array {
		if (isset($worker)) {
			return $this->getBestWorker($worker)?->getBuddylist() ?? [];
		}
		$buddylist = [];
		foreach ($this->connections as $id => $client) {
			foreach ($client->getBuddylist() as $uid => $online) {
				$buddylist[$uid] = $online;
			}
		}
		return $buddylist;
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
	public function isOnline(int $uid, bool $cacheOnly=true, ?string $worker=null): ?bool {
		$online = null;
		if (!isset($worker)) {
			foreach ($this->connections as $id => $client) {
				$online ??= $client->isOnline($uid, true);
			}
		}
		return $online ?? $this->getBestWorker($worker)?->isOnline($uid, $cacheOnly);
	}

	protected function triggerOnReady(): void {
		$this->isReady = true;
		$this->logger?->notice("Bot is now fully ready");
		while (null !== ($callback = array_shift($this->readyListeners))) {
			$this->logger?->debug("Calling {closure}", [
				"closure" => Utils::closureToString($callback),
			]);
			try {
				$callback();
			} catch (Throwable $e) {
				$this->logger?->error("Error calling {closure}: {error}", [
					"closure" => Utils::closureToString($callback),
					"error" => $e->getMessage(),
					"exception" => $e,
				]);
			}
		}
	}

	private function getBestWorker(?string $worker=null): ?Basic {
		$worker ??= $this->mainCharacter ?? array_keys($this->connections)[0];
		if (!isset($this->connections[$worker])) {
			$worker = array_keys($this->connections)[0];
		}
		return $this->connections[$worker] ?? null;
	}

	private function workerLoop(WorkerThread $worker): void {
		while (($package = $worker->client->read()) !== null) {
			$workerPackage = new WorkerPackage(
				worker: $worker->config->character,
				package: $package,
				client: $worker->client
			);
			if (!$this->readQueue->isComplete()) {
				$this->readQueue->push($workerPackage);
			}
		}
		if (!$this->readQueue->isComplete()) {
			$this->readQueue->complete();
		}
	}

	private function clientLogin(WorkerConfig $config): WorkerThread {
		do {
			$this->logger?->notice("Connecting to server {server}", ["server" => $config->getServer()]);
			try {
				$connection = connect($config->getServer());
			} catch (\Throwable $e) {
				$this->logger?->error("Cannot connect to {server}: {error}. Retrying in {delay}s", [
					"server" => $config->getServer(),
					"error" => $e->getMessage(),
					"delay" => 5,
					"exception" => $e,
				]);
				delay(5);
			}
		} while (!isset($connection));
		$this->logger?->info("Connected to {server}", ["server" => $config->getServer()]);
		$client = new Basic(
			logger: $this->logger,
			parser: $this->parser ?? Parser::createDefault(),
			bucket: $this->bucket,
			connection: new \AO\Connection(
				logger: $this->logger,
				reader: $connection,
				writer: $connection
			),
		);
		$client->login(
			username: $config->username,
			password: $config->password,
			character: Utils::normalizeCharacter($config->character),
		);
		$this->logger?->notice("Successfully logged in {character}", [
			"character" => $config->character,
		]);
		return new WorkerThread(
			config: $config,
			client: $client,
			socket: $connection,
		);
	}
}
