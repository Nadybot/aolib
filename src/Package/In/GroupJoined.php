<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\{Group, Package};

class GroupJoined extends Package\In {
	public function __construct(
		public Group\Id $groupId,
		public string $groupName,
		public int $flags,
		public string $unknown,
	) {
		parent::__construct(Package\Type::PublicChannelJoined);
	}

	public static function getFormat(): string {
		return "GSIS";
	}
}
