<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class CharacterLookupResult extends Package\In {
	public function __construct(
		public int $charId,
		public string $name,
	) {
		parent::__construct(Package\Type::CharacterLookup);
	}

	public function getUid(): ?int {
		return $this->charId === 0xFFFFFFFF ? null : $this->charId;
	}

	public static function getFormat(): string {
		return "IS";
	}
}
