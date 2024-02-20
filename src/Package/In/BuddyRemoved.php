<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class BuddyRemoved extends Package\In {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Package\Type::BuddyRemove);
	}

	public static function getFormat(): string {
		return "I";
	}
}
