<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class BuddyRemoved extends Package\InPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Package\PackageType::BuddyRemove);
	}

	public static function getFormat(): string {
		return "I";
	}
}
