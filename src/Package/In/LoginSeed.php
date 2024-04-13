<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class LoginSeed extends Package\InPackage {
	public function __construct(
		public readonly string $serverSeed,
	) {
		parent::__construct(Package\PackageType::LoginSeed);
	}

	public static function getFormat(): string {
		return "S";
	}
}
