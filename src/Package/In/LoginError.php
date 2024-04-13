<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class LoginError extends Package\InPackage {
	public function __construct(
		public string $error,
	) {
		parent::__construct(Package\PackageType::LoginError);
	}

	public static function getFormat(): string {
		return "S";
	}
}
