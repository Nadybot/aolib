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

	/** @return list<array{int, int}> */
	public static function exampleMMDBInstanceCounts(): array {
		return [
			[20_000, 38],
		];
	}

	#[DataProvider('exampleMMDBInstanceCounts')]
	public function testFindAllInstances(int $categoryId, int $expectedCount): void {
		$mmdb = AsyncMMDBClient::createDefault();
		$this->assertCount($expectedCount, $mmdb->findAllInstancesInCategory($categoryId));
	}

	public function testGetCategories(): void {
		$mmdb = AsyncMMDBClient::createDefault();
		$foundCategories = $mmdb->getCategories();
		$this->assertNotNull($foundCategories);
		$this->assertCount(52, $foundCategories);
	}
}
