<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class PrivategroupMessage extends InPackage {
	public function __construct(
		public int $channelId,
		public int $userId,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Type::PRIVGRP_MESSAGE);
	}
}
