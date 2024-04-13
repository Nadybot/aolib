<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class PrivateChannelKickAll extends Package\OutPackage {
	public function __construct() {
		parent::__construct(Package\PackageType::PrivateChannelKickAll);
	}

	public static function getFormat(): string {
		return "";
	}
}
