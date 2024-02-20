<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class PrivateChannelMessage extends Package\Out {
	public function __construct(
		public int $channelId,
		public string $message,
		public string $extra="\0",
	) {
		parent::__construct(Package\Type::PrivateChannelMessage);
	}

	public static function getFormat(): string {
		return "ISS";
	}
}
