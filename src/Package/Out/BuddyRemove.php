<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class BuddyRemove extends Package\OutPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Package\PackageType::BuddyRemove);
	}

	public static function getFormat(): string {
		return "I";
	}
}
