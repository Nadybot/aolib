<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivateChannelInviteRefused extends InPackage {
	public function __construct(
		public int $channelId,
		public int $charId,
	) {
		parent::__construct(Type::PrivateChannelInviteRefused);
	}
}