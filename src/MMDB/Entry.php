<?php declare(strict_types=1);

namespace AO\MMDB;

final class Entry {
	public function __construct(
		public int $id,
		public int $offset,
	) {
	}
}
