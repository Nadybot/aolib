<?php declare(strict_types=1);

namespace AO;

class Character {
	final public function __construct(
		public readonly int $uid,
		public readonly string $name,
		public readonly int $level,
		public readonly bool $online,
	) {
	}
}
