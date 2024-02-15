<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class Tell extends InPackage {
	public function __construct(
		public int $sender,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Type::MSG_PRIVATE);
	}
}
