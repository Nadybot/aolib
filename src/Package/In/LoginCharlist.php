<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package;

class LoginCharlist extends Package\In {
	/**
	 * @param int[]    $charIds
	 * @param string[] $characters
	 * @param int[]    $levels
	 * @param bool[]   $online
	 */
	public function __construct(
		public array $charIds,
		public array $characters,
		public array $levels,
		public array $online,
	) {
		parent::__construct(Package\Type::LoginCharlist);
	}

	public static function getFormat(): string {
		return "isib";
	}
}
