<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class CharacterLookup extends Package\Out {
	public function __construct(
		public string $name,
	) {
		parent::__construct(Package\Type::CharacterLookup);
	}

	public static function getFormat(): string {
		return "S";
	}
}
