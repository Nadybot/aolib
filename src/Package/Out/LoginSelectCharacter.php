<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class LoginSelectCharacter extends OutPackage {
	public function __construct(
		public int $uid,
	) {
		parent::__construct(Type::LOGIN_SELECT);
	}
}
