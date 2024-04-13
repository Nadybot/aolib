<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\{Group, Package};

class GroupDataSet extends Package\OutPackage {
	public function __construct(
		public Group\GroupId $groupId,
		public int $status,
		public string $extra="\0",
	) {
		parent::__construct(Package\PackageType::PublicChannelDataSet);
	}

	public static function getFormat(): string {
		return "GIS";
	}
}
