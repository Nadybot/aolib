<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class PrivateChannelInvite extends Package\Out {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Package\Type::PrivateChannelKick);
	}

	public static function getFormat(): string {
		return "I";
	}
}
