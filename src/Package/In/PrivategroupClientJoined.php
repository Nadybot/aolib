<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivategroupClientJoined extends InPackage {
	public function __construct(
		public int $channelId,
		public int $userId,
	) {
		parent::__construct(Type::PRIVGRP_CLIJOIN);
	}
}
