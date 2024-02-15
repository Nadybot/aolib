<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\{Attributes as Attr, Type};

class LoginRequest extends OutPackage {
	public function __construct(
		#[Attr\Param(1)]
		public string $username,
		#[Attr\Param(2)]
		public string $key,
		#[Attr\Param(0)]
		public int $zero=0,
	) {
		parent::__construct(Type::LOGIN_REQUEST);
	}
}
