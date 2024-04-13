<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class SystemMessage extends Package\InPackage {
	public function __construct(
		public int $clientId,
		public int $windowId,
		public int $messageId,
		public string $message,
	) {
		parent::__construct(Package\PackageType::SystemMessage);
	}

	public static function getFormat(): string {
		return 'IIIS';
	}
}
