<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class CharacterLookupResult extends InPackage {
	public function __construct(
		public int $charId,
		public string $name,
	) {
		parent::__construct(Type::CharacterLookup);
	}

	public function getUid(): ?int {
		return $this->charId === 0xFFFFFFFF ? null : $this->charId;
	}
}