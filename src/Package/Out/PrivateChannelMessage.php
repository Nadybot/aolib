<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class PrivateChannelMessage extends Package\OutPackage {
	public function __construct(
		public int $channelId,
		public string $message,
		public string $extra="\0",
	) {
		parent::__construct(Package\PackageType::PrivateChannelMessage);
	}

	public static function getFormat(): string {
		return "ISS";
	}
}
