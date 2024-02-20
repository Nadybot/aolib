<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivateChannelClientLeft extends InPackage {
	public function __construct(
		public int $channelId,
		public int $charId,
	) {
		parent::__construct(Type::PrivateChannelClientLeft);
	}

	public static function getFormat(): string {
		return "II";
	}
}
