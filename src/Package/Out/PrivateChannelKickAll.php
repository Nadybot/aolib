<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class PrivateChannelKickAll extends OutPackage {
	public function __construct() {
		parent::__construct(Type::PrivateChannelKickAll);
	}
}
