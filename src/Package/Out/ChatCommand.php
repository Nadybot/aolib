<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\Type;

class ChatCommand extends OutPackage {
	/** @param string[] $commands */
	public function __construct(
		public array $commands,
		public int $windowId=0
	) {
		parent::__construct(Type::ChatCommand);
	}

	public static function getFormat(): string {
		return "sI";
	}
}
