<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\{ExtendedMessage, Group, Package};

class GroupMessage extends Package\In {
	public function __construct(
		public Group\Id $groupId,
		public int $charId,
		public string $message,
		public string $extra,
		public ?ExtendedMessage $extendedMessage=null,
	) {
		parent::__construct(Package\Type::PublicChannelMessage);
	}

	public static function getFormat(): string {
		return "GISS";
	}
}
