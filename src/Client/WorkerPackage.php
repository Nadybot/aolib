<?php declare(strict_types=1);

namespace AO\Client;

use AO\Package;

class WorkerPackage {
	public function __construct(
		public readonly string $worker,
		public readonly Package\In $package,
		public readonly Basic $client,
	) {
	}
}
