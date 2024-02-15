<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class KickAllFromPrivategroup extends OutPackage {
	public function __construct() {
		parent::__construct(Type::PRIVGRP_KICKALL);
	}
}
