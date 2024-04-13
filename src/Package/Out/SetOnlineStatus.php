<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class SetOnlineStatus extends Package\OutPackage {
	public function __construct(
		public bool $online,
	) {
		parent::__construct(Package\PackageType::SetOnlineStatus);
	}

	public static function getFormat(): string {
		return "B";
	}
}
