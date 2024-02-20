<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class VicinityMessage extends Package\In {
	public function __construct(
		public int $charId,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Package\Type::VicinityMessage);
	}

	public static function getFormat(): string {
		return "ISS";
	}
}
