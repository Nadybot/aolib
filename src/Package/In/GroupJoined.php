<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\{GroupId, Type};

class GroupJoined extends InPackage {
	public function __construct(
		public GroupId $groupId,
		public string $groupName,
		public int $flags,
		public string $unknown,
	) {
		parent::__construct(Type::PublicChannelJoined);
	}

	public static function getFormat(): string {
		return "GSIS";
	}
}
