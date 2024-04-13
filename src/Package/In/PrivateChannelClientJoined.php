<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class PrivateChannelClientJoined extends Package\InPackage {
	public function __construct(
		public int $channelId,
		public int $charId,
	) {
		parent::__construct(Package\PackageType::PrivateChannelClientJoined);
	}

	public static function getFormat(): string {
		return "II";
	}
}
