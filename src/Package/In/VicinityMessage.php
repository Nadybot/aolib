<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class VicinityMessage extends Package\InPackage {
	public function __construct(
		public int $charId,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Package\PackageType::VicinityMessage);
	}

	public static function getFormat(): string {
		return "ISS";
	}
}
