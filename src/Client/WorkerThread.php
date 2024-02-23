<?php declare(strict_types=1);

namespace AO\Client;

use Amp\Socket\Socket;

class WorkerThread {
	public function __construct(
		public readonly WorkerConfig $config,
		public readonly Basic $client,
		public readonly Socket $socket,
	) {
	}
}
