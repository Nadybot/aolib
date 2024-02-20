<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class Tell extends OutPackage {
	public function __construct(
		public int $charId,
		public string $message,
		public string $extra="\0",
	) {
		parent::__construct(Type::PrivateMessage);
	}

	public static function getFormat(): string {
		return "ISS";
	}
}
