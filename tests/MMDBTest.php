<?php declare(strict_types=1);

namespace AO\Tests;

use function Amp\File\openFile;
use AO\MMDB\AsyncMMDBClient;
use Beste\Psr\Log\TestLogger;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MMDBTest extends TestCase {
	public function testException(): void {
		$this->expectException(Exception::class);
		new AsyncMMDBClient(
			logger: TestLogger::create(),
			mmdb: openFile(__FILE__, 'rb'),
		);
	}

	/** @return list<array{int, int, string}> */
	public static function exampleMMDBTexts(): array {
		return [
			[20_000, 18_838_393, 'Removing %d #1{ 1:buddy | buddies }.'],
		];
	}

	#[DataProvider('exampleMMDBTexts')]
	public function testMessages(int $categoryId, int $messageId, string $expected): void {
		$mmdb = AsyncMMDBClient::createDefault();
		$this->assertSame($expected, $mmdb->getMessageString($categoryId, $messageId));
	}
}
