<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivateChannelMessage extends InPackage {
	public function __construct(
		public int $channelId,
		public int $charId,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Type::PrivateChannelMessage);
	}

	public static function getFormat(): string {
		return "IISS";
	}
}
