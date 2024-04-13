<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class CharacterLookup extends Package\OutPackage {
	public function __construct(
		public string $name,
	) {
		parent::__construct(Package\PackageType::CharacterLookup);
	}

	public static function getFormat(): string {
		return 'S';
	}
}
