<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class Ping extends InPackage {
	public function __construct(
		public readonly string $extra,
	) {
		parent::__construct(Type::Ping);
	}
}
