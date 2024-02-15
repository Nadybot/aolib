<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class BuddyAdded extends InPackage {
	public function __construct(
		public int $uid,
		public bool $online,
		public string $extra,
	) {
		parent::__construct(Type::BUDDY_ADD);
	}
}
