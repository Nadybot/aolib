<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class PrivateChannelMessage extends OutPackage {
	public function __construct(
		public int $channelId,
		public string $message,
		public string $extra="\0",
	) {
		parent::__construct(Type::PrivateChannelMessage);
	}

	public static function getFormat(): string {
		return "ISS";
	}
}
