<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class LoginCharlist extends InPackage {
	/**
	 * @param int[]    $uids
	 * @param string[] $characters
	 * @param int[]    $levels
	 * @param bool[]   $online
	 */
	public function __construct(
		public array $uids,
		public array $characters,
		public array $levels,
		public array $online,
	) {
		parent::__construct(Type::LOGIN_CHARLIST);
	}
}
