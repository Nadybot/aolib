<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\{Group, Package};

class ClientmodeGet extends Package\Out {
	public function __construct(
		public int $unknown1,
		public Group\Id $groupId,
	) {
		parent::__construct(Package\Type::ClientModeGet);
	}

	public static function getFormat(): string {
		return "IG";
	}
}
