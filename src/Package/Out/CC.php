<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class Cc extends OutPackage {
	/** @param string[] $unknown1 */
	public function __construct(
		public array $unknown1,
	) {
		parent::__construct(Type::CC);
	}
}
