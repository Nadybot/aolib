<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class BuddyAdd extends Package\Out {
	public function __construct(
		public int $charId,
		public string $extra="\1",
	) {
		parent::__construct(Package\Type::BuddyAdd);
	}

	public static function getFormat(): string {
		return "IS";
	}
}
