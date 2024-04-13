<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\{Group, Package};

class ClientmodeGet extends Package\OutPackage {
	public function __construct(
		public int $unknown1,
		public Group\GroupId $groupId,
	) {
		parent::__construct(Package\PackageType::ClientModeGet);
	}

	public static function getFormat(): string {
		return "IG";
	}
}
