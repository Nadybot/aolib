<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivateChannelKicked extends InPackage {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Type::PrivateChannelKick);
	}

	public static function getFormat(): string {
		return "I";
	}
}
