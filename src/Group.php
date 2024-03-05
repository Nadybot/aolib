<?php declare(strict_types=1);

namespace AO;

class Group {
	public const NO_WRITE = 0x00000002;
	public const NO_ASIAN = 0x00000020;
	public const MUTE =     0x01010000;
	public const LOG =      0x02020000;

	final public function __construct(
		public readonly Group\Id $id,
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
