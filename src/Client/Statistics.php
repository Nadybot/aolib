<?php declare(strict_types=1);

namespace AO\Client;

class Statistics {
	/**
	 * @param array<int,int> $packagesRead
	 * @param array<int,int> $packagesWritten
	 */
	public function __construct(
		public array $packagesRead=[],
		public int $bytesRead=0,
		public array $packagesWritten=[],
		public int $bytesWritten=0,
	) {
	}

	public function add(self $statistic): self {
		$combinedStats = new self(
			packagesRead: $this->packagesRead,
			packagesWritten: $this->packagesWritten,
			bytesRead: $this->bytesRead + $statistic->bytesRead,
			bytesWritten: $this->bytesWritten + $statistic->bytesWritten,
		);
		foreach ($statistic->packagesWritten as $type => $count) {
			$combinedStats->packagesWritten[$type] += $count;
		}
		foreach ($statistic->packagesRead as $type => $count) {
			$combinedStats->packagesRead[$type] += $count;
		}
		return $combinedStats;
	}
}
