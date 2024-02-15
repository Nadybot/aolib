<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class ChatNotice extends InPackage {
	public function __construct(
		public int $unknown1,
		public int $unknown2,
		public int $instanceId,
		public string $message,
	) {
		parent::__construct(Type::CHAT_NOTICE);
	}
}
