<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class LoginError extends InPackage {
	public function __construct(
		public string $error,
	) {
		parent::__construct(Type::LoginError);
	}
}
