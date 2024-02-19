<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class SimpleSystemMessage extends InPackage {
	public function __construct(
		public string $message,
	) {
		parent::__construct(Type::SimpleSystemMessage);
	}
}
