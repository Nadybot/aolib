<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class AddBuddy extends OutPackage {
	public function __construct(
		public int $uid,
		public string $extra="\1",
	) {
		parent::__construct(Type::BUDDY_ADD);
	}
}
