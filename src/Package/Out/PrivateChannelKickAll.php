<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class PrivateChannelKickAll extends Package\Out {
	public function __construct() {
		parent::__construct(Package\Type::PrivateChannelKickAll);
	}

	public static function getFormat(): string {
		return "";
	}
}
