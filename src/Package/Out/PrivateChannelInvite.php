<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class PrivateChannelInvite extends Package\OutPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Package\PackageType::PrivateChannelKick);
	}

	public static function getFormat(): string {
		return "I";
	}
}
