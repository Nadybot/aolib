<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\{ExtendedMessage, Group, Package};

class GroupMessage extends Package\InPackage {
	public function __construct(
		public Group\GroupId $groupId,
		public int $charId,
		public string $message,
		public string $extra,
		public ?ExtendedMessage $extendedMessage=null,
	) {
		parent::__construct(Package\PackageType::PublicChannelMessage);
	}

	public static function getFormat(): string {
		return 'GISS';
	}
}
