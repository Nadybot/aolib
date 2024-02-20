<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class PrivateChannelKicked extends Package\In {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Package\Type::PrivateChannelKick);
	}

	public static function getFormat(): string {
		return "I";
	}
}
