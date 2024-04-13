<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class PrivateChannelKicked extends Package\InPackage {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Package\PackageType::PrivateChannelKick);
	}

	public static function getFormat(): string {
		return "I";
	}
}
