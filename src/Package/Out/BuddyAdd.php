<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class BuddyAdd extends OutPackage {
	public function __construct(
		public int $charId,
		public string $extra="\1",
	) {
		parent::__construct(Type::BuddyAdd);
	}

	public static function getFormat(): string {
		return "IS";
	}
}
