<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class PrivateChannelJoin extends Package\Out {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Package\Type::PrivateChannelJoin);
	}

	public static function getFormat(): string {
		return "I";
	}
}
