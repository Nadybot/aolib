<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class AdmMuxInfo extends Package\In {
	/**
	 * @param int[] $unknown1
	 * @param int[] $unknown2
	 * @param int[] $unknown3
	 */
	public function __construct(
		public readonly array $unknown1,
		public readonly array $unknown2,
		public readonly array $unknown3,
	) {
		parent::__construct(Package\Type::AdmMuxInfo);
	}

	public static function getFormat(): string {
		return "iii";
	}
}
