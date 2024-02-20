<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class LoginSelectCharacter extends OutPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Type::LoginSelect);
	}

	public static function getFormat(): string {
		return "I";
	}
}
