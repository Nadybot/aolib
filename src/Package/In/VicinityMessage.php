<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class VicinityMessage extends InPackage {
	public function __construct(
		public int $charId,
		public string $message,
		public string $extra,
	) {
		parent::__construct(Type::VicinityMessage);
	}
}
