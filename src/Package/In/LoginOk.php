<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class LoginOk extends InPackage {
	public function __construct() {
		parent::__construct(Type::LoginOK);
	}

	public static function getFormat(): string {
		return "";
	}
}
