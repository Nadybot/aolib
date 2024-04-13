<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class LoginOk extends Package\InPackage {
	public function __construct() {
		parent::__construct(Package\PackageType::LoginOK);
	}

	public static function getFormat(): string {
		return '';
	}
}
