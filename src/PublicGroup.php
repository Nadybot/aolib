<?php declare(strict_types=1);

namespace AO;

use AO\Package\GroupId;

class PublicGroup {
	public const NO_WRITE = 0x00000002;
	public const NO_ASIAN = 0x00000020;
	public const MUTE =     0x01010000;
	public const LOG =      0x02020000;

	public function __construct(
		public GroupId $id,
		public string $name,
		public int $flags,
	) {
	}

	public function isWritable(): bool {
		return ($this->flags & self::NO_WRITE) === 0;
	}

	public function isMuted(): bool {
		return ($this->flags & self::MUTE) !== 0;
	}

	public function allowsAsian(): bool {
		return ($this->flags & self::NO_ASIAN) !== 0;
	}
}
