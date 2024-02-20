<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class SystemMessage extends InPackage {
	public function __construct(
		public int $clientId,
		public int $windowId,
		public int $instanceId,
		public string $message,
	) {
		parent::__construct(Type::SystemMessage);
	}

	public static function getFormat(): string {
		return "IIIS";
	}
}
