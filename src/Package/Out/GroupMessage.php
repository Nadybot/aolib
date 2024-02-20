<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\{Group, Package};

class GroupMessage extends Package\Out\RateLimited {
	public function __construct(
		public Group\Id $groupId,
		public string $message,
		public string $extra="\0",
	) {
		parent::__construct(Package\Type::PublicChannelMessage);
	}

	public static function getFormat(): string {
		return "GSS";
	}
}
