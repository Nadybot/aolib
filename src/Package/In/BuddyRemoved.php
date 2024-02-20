<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class BuddyRemoved extends InPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Type::BuddyRemove);
	}

	public static function getFormat(): string {
		return "I";
	}
}
