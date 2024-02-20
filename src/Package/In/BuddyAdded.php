<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class BuddyAdded extends Package\In {
	public function __construct(
		public int $charId,
		public bool $online,
		public string $extra,
	) {
		parent::__construct(Package\Type::BuddyAdd);
	}

	public static function getFormat(): string {
		return "IBS";
	}
}
