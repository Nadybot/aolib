<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class LoginSeed extends InPackage {
	public function __construct(
		public readonly string $serverSeed,
	) {
		parent::__construct(Type::LoginSeed);
	}

	public static function getFormat(): string {
		return "S";
	}
}
