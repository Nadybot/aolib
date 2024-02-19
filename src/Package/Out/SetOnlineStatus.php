<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class SetOnlineStatus extends OutPackage {
	public function __construct(
		public bool $online,
	) {
		parent::__construct(Type::SetOnlineStatus);
	}
}
