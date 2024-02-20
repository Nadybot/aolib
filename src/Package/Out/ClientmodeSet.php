<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class ClientmodeSet extends Package\Out {
	public function __construct(
		public int $unknown1,
		public int $unknown2,
		public int $unknown3,
		public int $unknown4,
	) {
		parent::__construct(Package\Type::ClientModeSet);
	}

	public static function getFormat(): string {
		return "IIII";
	}
}
