<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\{GroupId, Type};

class ClientmodeGet extends OutPackage {
	public function __construct(
		public int $unknown1,
		public GroupId $groupId,
	) {
		parent::__construct(Type::ClientModeGet);
	}

	public static function getFormat(): string {
		return "IG";
	}
}
