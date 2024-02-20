<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class Ping extends Package\In {
	public function __construct(
		public readonly string $extra,
	) {
		parent::__construct(Package\Type::Ping);
	}

	public static function getFormat(): string {
		return "S";
	}
}
