<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class PrivateChannelLeft extends Package\InPackage {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Package\PackageType::PrivateChannelLeft);
	}

	public static function getFormat(): string {
		return 'I';
	}
}
