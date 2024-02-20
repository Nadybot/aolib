<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\{Group, Package};

class GroupDataSet extends Package\Out {
	public function __construct(
		public Group\Id $groupId,
		public int $status,
		public string $extra="\0",
	) {
		parent::__construct(Package\Type::PublicChannelDataSet);
	}

	public static function getFormat(): string {
		return "GIS";
	}
}
