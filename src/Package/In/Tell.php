<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class Tell extends Package\InPackage {
	public function __construct(
		public int $charId,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Package\PackageType::PrivateMessage);
	}

	public static function getFormat(): string {
		return "ISS";
	}
}
