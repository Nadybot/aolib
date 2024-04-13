<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class SimpleSystemMessage extends Package\InPackage {
	public function __construct(
		public string $message,
	) {
		parent::__construct(Package\PackageType::SimpleSystemMessage);
	}

	public static function getFormat(): string {
		return 'S';
	}
}
