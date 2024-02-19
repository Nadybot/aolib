<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivateChannelInvited extends InPackage {
	public function __construct(
		public int $channelId,
	) {
		parent::__construct(Type::PrivateChannelKick);
	}
}
