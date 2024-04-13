<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class LoginSelectCharacter extends Package\OutPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Package\PackageType::LoginSelect);
	}

	public static function getFormat(): string {
		return 'I';
	}
}
