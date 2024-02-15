<?php declare(strict_types=1);

namespace AO;

class Utils {
	public static function normalizeCharacter(string $character): string {
		return ucfirst(strtolower($character));
	}
}
