<?php declare(strict_types=1);

namespace AO\Package\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class Param {
	public function __construct(
		public readonly int $position
	) {
	}
}
