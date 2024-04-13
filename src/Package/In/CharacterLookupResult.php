<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class CharacterLookupResult extends Package\InPackage {
	public function __construct(
		public int $charId,
		public string $name,
	) {
		parent::__construct(Package\PackageType::CharacterLookup);
	}

	public function getUid(): ?int {
		return $this->charId === 0xFF_FF_FF_FF ? null : $this->charId;
	}

	public static function getFormat(): string {
		return 'IS';
	}
}
