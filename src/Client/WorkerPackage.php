<?php declare(strict_types=1);

namespace AO\Client;

use AO\Package;
use Stringable;

class WorkerPackage implements Stringable {
	public function __construct(
		public readonly string $worker,
		public readonly Package\InPackage $package,
		public readonly SingleClient $client,
	) {
	}

	public function __toString(): string {
		$classes = explode("\\", get_class($this));
		$class = array_pop($classes);
		return "<{$class}>{".
				"worker={$this->worker},".
				"package=" . (string)$this->package . ",".
				"client=<BasicClient>}";
	}
}
