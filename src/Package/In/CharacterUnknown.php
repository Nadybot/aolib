<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class CharacterUnknown extends InPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Type::CharacterUnknown);
	}
}
