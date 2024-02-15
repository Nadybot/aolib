<?php declare(strict_types=1);

namespace AO\Tests;

use function Amp\File\openFile;
use AO\MMDB\AsyncClient;
use Beste\Psr\Log\TestLogger;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MMDBTest extends TestCase {
	public function testException(): void {
		$this->expectException(Exception::class);
		new AsyncClient(
			TestLogger::create(),
			openFile(__FILE__, "rb"),
		);
	}

	/** @return list<array{int, int, string}> */
	public static function exampleMMDBTexts(): array {
		return [
			[20000, 18838393, "Removing %d #1{ 1:buddy | buddies }."],
		];
	}

	#[DataProvider('exampleMMDBTexts')]
	public function testMessages(int $categoryId, int $instanceId, string $expected): void {
		$mmdb = AsyncClient::createDefault(TestLogger::create());
		$this->assertSame($expected, $mmdb->getMessageString($categoryId, $instanceId));
	}
}
