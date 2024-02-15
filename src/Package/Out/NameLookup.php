<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class NameLookup extends OutPackage {
	public function __construct(
		public string $name,
	) {
		parent::__construct(Type::CLIENT_LOOKUP);
	}
}
