<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivateChannelClientJoined extends InPackage {
	public function __construct(
		public int $channelId,
		public int $charId,
	) {
		parent::__construct(Type::PrivateChannelClientJoined);
	}

	public static function getFormat(): string {
		return "II";
	}
}
