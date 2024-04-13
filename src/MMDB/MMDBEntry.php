<?php declare(strict_types=1);

namespace AO\MMDB;

final class MMDBEntry {
	public function __construct(
		public int $id,
		public int $offset,
	) {
	}
}
