<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class BuddyState extends Package\InPackage {
	public function __construct(
		public int $charId,
		public bool $online,
		public string $extra,
	) {
		parent::__construct(Package\PackageType::BuddyAdd);
	}

	public static function getFormat(): string {
		return 'IBS';
	}
}
