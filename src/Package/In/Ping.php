<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class Ping extends Package\InPackage {
	public function __construct(
		public readonly string $extra,
	) {
		parent::__construct(Package\PackageType::Ping);
	}

	public static function getFormat(): string {
		return 'S';
	}
}
