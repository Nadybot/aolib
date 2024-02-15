<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\{GroupId, Type};

class GetClientmode extends OutPackage {
	public function __construct(
		public int $unknown1,
		public GroupId $group,
	) {
		parent::__construct(Type::CLIENTMODE_GET);
	}
}
