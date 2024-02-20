<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class SimpleSystemMessage extends Package\In {
	public function __construct(
		public string $message,
	) {
		parent::__construct(Package\Type::SimpleSystemMessage);
	}

	public static function getFormat(): string {
		return "S";
	}
}
