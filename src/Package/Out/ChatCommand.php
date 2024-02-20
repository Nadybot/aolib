<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;

class ChatCommand extends Package\Out {
	/** @param string[] $commands */
	public function __construct(
		public array $commands,
		public int $windowId=0
	) {
		parent::__construct(Package\Type::ChatCommand);
	}

	public static function getFormat(): string {
		return "sI";
	}
}
