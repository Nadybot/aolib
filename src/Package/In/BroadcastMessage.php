<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class BroadcastMessage extends InPackage {
	public function __construct(
		public string $sender,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Type::BroadcastMessage);
	}

	public static function getFormat(): string {
		return "SSS";
	}
}
