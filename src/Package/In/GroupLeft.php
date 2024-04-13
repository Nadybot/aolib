<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\{Group, Package};

class GroupLeft extends Package\InPackage {
	public function __construct(
		public Group\GroupId $groupId,
	) {
		parent::__construct(Package\PackageType::PublicChannelLeft);
	}

	public static function getFormat(): string {
		return "G";
	}
}
