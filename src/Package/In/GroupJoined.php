<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\{Group, Package};

class GroupJoined extends Package\InPackage {
	public function __construct(
		public Group\GroupId $groupId,
		public string $groupName,
		public int $flags,
		public string $unknown,
	) {
		parent::__construct(Package\PackageType::PublicChannelJoined);
	}

	public static function getFormat(): string {
		return 'GSIS';
	}
}
