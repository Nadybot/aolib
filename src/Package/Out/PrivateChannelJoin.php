<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class PrivateChannelJoin extends OutPackage {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Type::PrivateChannelJoin);
	}

	public static function getFormat(): string {
		return "I";
	}
}
