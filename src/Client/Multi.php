<?php declare(strict_types=1);

namespace AO\Client;

use function Amp\Future\awaitAll;
use function Amp\Socket\connect;
use function Amp\{async, delay};

use AO\{Package, Parser, Utils};
use InvalidArgumentException;
use Nadylib\LeakyBucket\LeakyBucket;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

class Multi {
	/** @var array<string,Basic> */
	private array $connections = [];

	/** @var WorkerConfig[] */
	private array $configs = [];

	/** @var list<?WorkerPackage> */
	private array $readQueue = [];

	/** @var Suspension<?WorkerPackage> */
	private ?Suspension $queueProcessor = null;

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
		foreach ($workers[1] as $worker) {
			$this->connections[$worker->config->character] = $worker->client;
			async($this->workerLoop(...), $worker);
		}
		EventLoop::onSignal(SIGINT, function (string $cancellation) {
			foreach ($this->connections as $id => $connection) {
				$connection->disconnect();
			}
			if (isset($this->queueProcessor)) {
				$this->queueProcessor->resume(null);
			} else {
				$this->readQueue []= null;
			}
			EventLoop::cancel($cancellation);
		});
	}

	public function write(Package\Out $package, ?string $worker=null): void {
		$this->getBestWorker()?->write($package);
	}

	public function read(): ?WorkerPackage {
		if (count($this->readQueue)) {
			return array_shift($this->readQueue);
		}
		$this->queueProcessor = EventLoop::getSuspension();
		$this->logger?->debug("Suspending read thread");
		$package = $this->queueProcessor->suspend();
		$this->logger?->debug("Read thread resumed");
		$this->queueProcessor = null;
		return $package;
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

	private function getBestWorker(?string $worker=null): ?Basic {
		$worker ??= $this->mainCharacter ?? array_keys($this->connections)[0];
		return $this->connections[$worker] ?? null;
	}

	private function reportReadPackage(?WorkerPackage $package): void {
		if (isset($this->queueProcessor)) {
			$queueProcessor = $this->queueProcessor;
			$this->queueProcessor = null;
			$this->logger?->debug("Resuming read thread");
			$queueProcessor->resume($package);
		} elseif (isset($package)) {
			$this->logger?->debug("Queueing read package into read queue");
			$this->readQueue []= $package;
		}
	}

	private function workerLoop(WorkerThread $worker): void {
		while (($package = $worker->client->read()) !== null) {
			$workerPackage = new WorkerPackage(
				worker: $worker->config->character,
				package: $package,
				client: $worker->client
			);
			$this->reportReadPackage($workerPackage);
		}
		$this->reportReadPackage(null);
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
