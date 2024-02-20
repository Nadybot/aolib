<?php declare(strict_types=1);

namespace AO;

class Utils {
	/**
	 * Normalize the name of an AO-character: all lowercase, except for the first char
	 *
	 * @param string $character Name of the character
	 */
	public static function normalizeCharacter(string $character): string {
		return ucfirst(strtolower($character));
	}
}
