<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\{GroupId, Type};

class GroupSetClientmode extends OutPackage {
	public function __construct(
		public GroupId $group,
		public int $unknown1,
		public int $unknown2,
		public int $unknown3,
		public int $unknown4,
	) {
		parent::__construct(Type::GROUP_CM_SET);
	}
}
