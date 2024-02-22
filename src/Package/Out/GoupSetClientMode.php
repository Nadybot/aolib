<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\{Group, Package};

class GroupSetClientMode extends Package\Out {
	public function __construct(
		public Group\Id $groupId,
		public int $unknown1,
		public int $unknown2,
		public int $unknown3,
		public int $unknown4,
	) {
		parent::__construct(Package\Type::PublicChannelSetClientMode);
	}

	public static function getFormat(): string {
		return "GIIII";
	}
}
