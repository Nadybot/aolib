<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class CharacterUnknown extends Package\In {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Package\Type::CharacterUnknown);
	}

	public static function getFormat(): string {
		return "I";
	}
}
