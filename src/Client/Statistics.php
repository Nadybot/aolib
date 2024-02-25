<?php declare(strict_types=1);

namespace AO\Client;

class Statistics {
	/** @param array<int,int> $packagesWritten */
	public function __construct(
		public int $packagesRead=0,
		public int $bytesRead=0,
		public array $packagesWritten=[],
		public int $bytesWritten=0,
	) {
	}

	public function add(self $statistic): self {
		$combinedStats = new self(
			packagesRead: $this->packagesRead + $statistic->packagesRead,
			packagesWritten: $this->packagesWritten,
			bytesRead: $this->bytesRead + $statistic->bytesRead,
			bytesWritten: $this->bytesWritten + $statistic->bytesWritten,
		);
		foreach ($statistic->packagesWritten as $type => $count) {
			$combinedStats->packagesWritten[$type] += $count;
		}
		return $combinedStats;
	}
}
