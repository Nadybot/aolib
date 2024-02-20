<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class LoginOk extends Package\In {
	public function __construct() {
		parent::__construct(Package\Type::LoginOK);
	}

	public static function getFormat(): string {
		return "";
	}
}
