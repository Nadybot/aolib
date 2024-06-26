<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class Tell extends Package\Out\RateLimited {
	public function __construct(
		public int $charId,
		public string $message,
		public string $extra="\0",
	) {
		parent::__construct(Package\PackageType::PrivateMessage);
	}

	public static function getFormat(): string {
		return 'ISS';
	}
}
