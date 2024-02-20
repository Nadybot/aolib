<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivateChannelLeft extends InPackage {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Type::PrivateChannelLeft);
	}

	public static function getFormat(): string {
		return "I";
	}
}
