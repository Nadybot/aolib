<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\{GroupId, Type};

class GroupDataSet extends OutPackage {
	public function __construct(
		public GroupId $groupId,
		public int $status,
		public string $extra="\0",
	) {
		parent::__construct(Type::PublicChannelDataSet);
	}
}
