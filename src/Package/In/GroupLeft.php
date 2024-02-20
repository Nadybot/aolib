<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\{Group, Package};

class GroupLeft extends Package\In {
	public function __construct(
		public Group\Id $groupId,
	) {
		parent::__construct(Package\Type::PublicChannelLeft);
	}

	public static function getFormat(): string {
		return "G";
	}
}
