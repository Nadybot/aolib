<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\{Group, Package};

class GroupSetClientMode extends Package\OutPackage {
	public function __construct(
		public Group\GroupId $groupId,
		public int $unknown1,
		public int $unknown2,
		public int $unknown3,
		public int $unknown4,
	) {
		parent::__construct(Package\PackageType::PublicChannelSetClientMode);
	}

	public static function getFormat(): string {
		return 'GIIII';
	}
}
