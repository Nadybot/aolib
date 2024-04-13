<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class CharacterUnknown extends Package\InPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Package\PackageType::CharacterUnknown);
	}

	public static function getFormat(): string {
		return "I";
	}
}
