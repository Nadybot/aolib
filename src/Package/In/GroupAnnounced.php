<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\{GroupId, Type};

class GroupAnnounced extends InPackage {
	public function __construct(
		public GroupId $groupId,
		public string $groupName,
		public int $flags,
		public string $unknown2,
	) {
		parent::__construct(Type::GROUP_ANNOUNCE);
	}
}
