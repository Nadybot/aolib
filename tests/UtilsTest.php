<?php declare(strict_types=1);

namespace AO\Tests;

use AO\Package\PackageType;
use AO\Utils;
use Closure;
use PHPUnit\Framework\Attributes\{DataProvider, Small};
use PHPUnit\Framework\TestCase;
use WeakReference;

#[Small]
final class UtilsTest extends TestCase {
	/** @return list<array{0:string,1:string}> */
	public static function exampleNormalizedNames(): array {
		return [
			['pIgTail', 'Pigtail'],
			['Pigtail', 'Pigtail'],
			['pIGTAIL', 'Pigtail'],
			['pI1TAIL', 'Pi1tail'],
		];
	}

	#[DataProvider('exampleNormalizedNames')]
	public function testNormalization(string $wrong, string $correct): void {
		$this->assertSame($correct, Utils::normalizeCharacter($wrong));
	}

	/** @return list<array{0:string,1:Closure}> */
	public static function exampleClosures(): array {
		return [
			["json_encode()", json_encode(...)],
			["ord()", ord(...)],
			[
				__CLASS__ . "::" . __FUNCTION__ . "()",
				self::exampleClosures(...),
			],
			[
				__CLASS__ . "::" . __FUNCTION__ . "()",
				Closure::fromCallable([self::class, __FUNCTION__]),
			],
			[
				__CLASS__ . "::{closure}",
				static function () {
				},
			],
			[__CLASS__ . "->{closure}", fn () => 0],
			[
				WeakReference::class . "->get()",
				WeakReference::create(new \stdClass())->get(...),
			],
			[
				PackageType::class . "->classIn()",
				PackageType::AdmMuxInfo->classIn(...),
			],
		];
	}

	/** @phpstan-param non-empty-string $expected */
	#[DataProvider('exampleClosures')]
	public function testClosureToString(string $expected, Closure $closure): void {
		$parts = explode(" ", Utils::closureToString($closure), 2);
		$this->assertSame($expected, $parts[0]);
	}
}
