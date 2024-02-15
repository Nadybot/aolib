<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\{ExtendedMessage, GroupId, Type};

class GroupMessage extends InPackage {
	public function __construct(
		public GroupId $group,
		public int $senderId,
		public string $message,
		public string $extra,
		public ?ExtendedMessage $extendedMessage=null,
	) {
		parent::__construct(Type::GROUP_MESSAGE);
	}
}
