<?php declare(strict_types=1);

namespace AO;

class Group {
	public const NO_WRITE = 0x00_00_00_02;
	public const NO_ASIAN = 0x00_00_00_20;
	public const MUTE =     0x01_01_00_00;
	public const LOG =      0x02_02_00_00;

	final public function __construct(
		public readonly Group\GroupId $id,
		public readonly string $name,
		public readonly int $flags,
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
