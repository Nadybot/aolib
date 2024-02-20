<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\{GroupId, Type};

class GroupLeft extends InPackage {
	public function __construct(
		public GroupId $groupId,
	) {
		parent::__construct(Type::PublicChannelLeft);
	}

	public static function getFormat(): string {
		return "G";
	}
}
