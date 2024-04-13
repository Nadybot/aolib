<?php declare(strict_types=1);

namespace AO\Client;

use Amp\Socket\Socket;
use Stringable;

class WorkerFiber implements Stringable {
	public function __construct(
		public readonly WorkerConfig $config,
		public readonly SingleClient $client,
		public readonly Socket $socket,
	) {
	}

	public function __toString(): string {
		$classes = explode("\\", get_class($this));
		$class = array_pop($classes);
		return "<{$class}>{".
				"config=" . (string)$this->config . ",".
				"socket=<Amp\\Socket\\Socket>,".
				"client=<BasicClient>}";
	}
}
