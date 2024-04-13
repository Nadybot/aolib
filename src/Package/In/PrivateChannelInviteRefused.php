<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class PrivateChannelInviteRefused extends Package\InPackage {
	public function __construct(
		public int $channelId,
		public int $charId,
	) {
		parent::__construct(Package\PackageType::PrivateChannelInviteRefused);
	}

	public static function getFormat(): string {
		return "II";
	}
}
