<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class Ping extends OutPackage {
	public function __construct(
		public string $extra="\0",
	) {
		parent::__construct(Type::PING);
	}
}
