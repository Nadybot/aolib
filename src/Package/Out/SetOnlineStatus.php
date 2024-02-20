<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class SetOnlineStatus extends Package\Out {
	public function __construct(
		public bool $online,
	) {
		parent::__construct(Package\Type::SetOnlineStatus);
	}

	public static function getFormat(): string {
		return "B";
	}
}
