<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\{ExtendedMessage, GroupId, Type};

class GroupMessage extends InPackage {
	public function __construct(
		public GroupId $groupId,
		public int $charId,
		public string $message,
		public string $extra,
		public ?ExtendedMessage $extendedMessage=null,
	) {
		parent::__construct(Type::PublicChannelMessage);
	}

	public static function getFormat(): string {
		return "GISS";
	}
}
