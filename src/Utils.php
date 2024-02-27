<?php declare(strict_types=1);

namespace AO;

use Closure;
use ReflectionFunction;

class Utils {
	/**
	 * Normalize the name of an AO-character: all lowercase, except for the first char
	 *
	 * @param string $character Name of the character
	 */
	public static function normalizeCharacter(string $character): string {
		return ucfirst(strtolower($character));
	}

	public static function closureToString(Closure $closure): string {
		$result = "";
		$refCallback = new ReflectionFunction($closure);
		$class = $refCallback->getClosureCalledClass();
		$function = $refCallback->getShortName();
		if (isset($class)) {
			$result .= $class->getName().
				(($refCallback->isStatic()) ? "::" : "->").
				$function;
		} else {
			if (!$refCallback->inNamespace()) {
				$result .= $refCallback->getNamespaceName();
			}
			$result .= $function;
		}
		if (!str_ends_with($function, "}")) {
			$result .= "()";
		}
		$fileName = $refCallback->getFileName();
		if ($fileName !== false) {
			$result .= " ({$fileName}";
			$startLine = $refCallback->getStartLine();
			if ($startLine !== false) {
				$result .= ":{$startLine}";
			}
			$result .= ")";
		}
		return $result;
	}
}
