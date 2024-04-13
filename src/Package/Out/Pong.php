<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class Pong extends Package\OutPackage {
	public function __construct(
		public string $extra="\0",
	) {
		parent::__construct(Package\PackageType::Ping);
	}

	public static function getFormat(): string {
		return 'S';
	}
}
