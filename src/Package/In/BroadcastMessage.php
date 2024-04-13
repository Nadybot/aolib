<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class BroadcastMessage extends Package\InPackage {
	public function __construct(
		public string $sender,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Package\PackageType::BroadcastMessage);
	}

	public static function getFormat(): string {
		return 'SSS';
	}
}
