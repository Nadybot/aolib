<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class BuddyRemove extends OutPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Type::BuddyRemove);
	}
}
