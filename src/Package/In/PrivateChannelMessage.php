<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class PrivateChannelMessage extends Package\In {
	public function __construct(
		public int $channelId,
		public int $charId,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Package\Type::PrivateChannelMessage);
	}

	public static function getFormat(): string {
		return "IISS";
	}
}
