<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class PrivateChannelInvite extends OutPackage {
	public function __construct(
		public int $charId,
	) {
		parent::__construct(Type::PrivateChannelKick);
	}

	public static function getFormat(): string {
		return "I";
	}
}
