<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class ClientmodeSet extends Package\OutPackage {
	public function __construct(
		public int $unknown1,
		public int $unknown2,
		public int $unknown3,
		public int $unknown4,
	) {
		parent::__construct(Package\PackageType::ClientModeSet);
	}

	public static function getFormat(): string {
		return "IIII";
	}
}
