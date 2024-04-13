<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class PrivateChannelLeave extends Package\OutPackage {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Package\PackageType::PrivateChannelLeft);
	}

	public static function getFormat(): string {
		return "I";
	}
}
