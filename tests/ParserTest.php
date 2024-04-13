<?php declare(strict_types=1);

namespace AO\Tests;

use function Amp\File\openFile;
use function Safe\{hex2bin};

use AO\BinaryPackage\BinaryPackageIn;
use AO\Package\{In, Out, PackageType};
use AO\{BinaryPackage, Connection, Group, Package, Parser};
use Beste\Psr\Log\TestLogger;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase {
	public function testException(): void {
		$this->expectException(Exception::class);
		$package = new BinaryPackageIn(
			type: PackageType::ChatCommand,
			length: 0,
			body: "",
		);
		$parser = Parser::createDefault();
		$parser->parseBinaryPackage($package);
	}

	/** @return list<array{BinaryPackageIn, class-string}> */
	public static function exampleBinaryPacketsIn(): array {
		return [
			[
				new BinaryPackageIn(type: PackageType::BuddyAdd, length: 15, body: hex2bin("0000303900000001000101")),
				In\BuddyState::class,
			],
		];
	}

	/** @psalm-param class-string $expectedClass */
	#[DataProvider('exampleBinaryPacketsIn')]
	public function testInboundParser(BinaryPackageIn $package, string $expectedClass): void {
		$parser = Parser::createDefault();
		$result = $parser->parseBinaryPackage($package);
		$this->assertInstanceOf($expectedClass, $result);
	}

	/** @return array<string,array{Package}> */
	public static function examplePackages(): array {
		return [
			"In\\Ping" => [new In\Ping(extra: '')],
			"Out\\Ping" => [new Out\Pong(extra: random_bytes(random_int(1, 64)))],
			"In\\BuddyAdded" => [new In\BuddyState(charId: random_int(1, 2^32), online: false, extra: "abc")],
			"Out\\LoginRequest" => [new Out\LoginRequest(username: "Zero", key: "OMGsupers3cr3t", zero: 0)],
			"In\\GroupAnnounced" => [new In\GroupJoined(groupId: new Group\GroupId(type: Group\GroupType::Org, number: 12345), groupName: "Public", flags: 0, unknown: "")],
			"Out\\PrivateGroupMessage" => [new Out\PrivateChannelMessage(channelId: 1, message: "lol?")],
		];
	}

	#[DataProvider('examplePackages')]
	public function testPackagesBackAndForth(Package $package): void {
		$parser = Parser::createDefault();
		$binPackage = $package->toBinaryPackage();
		$this->assertInstanceOf(BinaryPackage::class, $binPackage);
		$reconverted = $parser->parseBinaryPackage($binPackage);
		$this->assertSame(serialize($package), serialize($reconverted));
	}

	public function testFromBinary1(): void {
		$data = [
			0, 65, 0, 58, 135, 0, 0, 0, 0, 76, 46, 67, 172, 0, 45, 97, 110, 121, 111, 105, 110, 101, 32, 116, 101,
			108, 108, 32, 109, 101, 32, 104, 111, 119, 32, 115, 110, 105, 112, 101, 32, 119, 111, 114,
			107, 115, 32, 102, 111, 114, 32, 97, 110, 32, 97, 103, 101, 110, 116, 63, 0, 0,
		];
		$parser = Parser::createDefault();
		$binary = join("", array_map("chr", $data));
		$binPackage = BinaryPackageIn::fromBinary($binary);
		$package = $parser->parseBinaryPackage($binPackage);
		$this->assertInstanceOf(expected: In\GroupMessage::class, actual: $package);
	}

	public function testFromBinary2(): void {
		$data = [
			0, 65, 0, 159, 10, 0, 0, 0, 0, 0, 0, 0, 0, 0, 146, 126, 38, 33, 33, 33, 38, 114, 33, 53, 98, 47, 82, 82,
			33, 33, 33, 56, 83, 33, 33, 33, 33, 33, 115, 34, 84, 104, 101, 32, 72, 32, 105, 115, 32,
			102, 111, 114, 32, 83, 110, 101, 97, 107, 105, 110, 103, 44, 32, 84, 114, 117, 101, 32, 83,
			116, 111, 114, 121, 115, 11, 68, 111, 117, 98, 108, 101, 102, 108, 105, 112, 82, 33, 33,
			33, 56, 83, 33, 33, 33, 33, 35, 115, 34, 80, 105, 122, 122, 105, 110, 103, 32, 111, 102,
			102, 32, 101, 118, 101, 114, 121, 111, 110, 101, 32, 105, 110, 32, 116, 104, 101, 32, 104,
			111, 117, 115, 101, 115, 16, 83, 116, 114, 101, 116, 32, 87, 101, 115, 116, 32, 66, 97,
			110, 107, 105, 33, 33, 33, 48, 70, 105, 33, 33, 33, 45, 110, 126, 0, 0,
		];
		$parser = Parser::createDefault();
		$binary = join("", array_map("chr", $data));
		$binPackage = BinaryPackageIn::fromBinary($binary);
		$package = $parser->parseBinaryPackage($binPackage);
		$this->assertInstanceOf(expected: In\GroupMessage::class, actual: $package);
	}

	public function testFromExampleLogin(): void {
		$file = openFile(__DIR__ . "/exampleLogin.bin", "rb");
		$connection = new Connection(logger: TestLogger::create(), reader: $file, writer: $file);
		$parser = Parser::createDefault();
		$classes = [
			In\LoginSeed::class,
			In\LoginCharlist::class,
			In\LoginOk::class,
			In\CharacterName::class,
			In\BroadcastMessage::class,
			In\CharacterName::class,
			In\BuddyState::class,
			In\CharacterName::class,
			In\BuddyState::class,
			In\CharacterName::class,
			In\BuddyState::class,
			In\CharacterName::class,
			In\BuddyState::class,
			In\CharacterName::class,
			In\BuddyState::class,
			In\CharacterName::class,
			In\BuddyState::class,
			In\CharacterName::class,
			In\BuddyState::class,
			In\CharacterName::class,
			In\BuddyState::class,
			In\GroupJoined::class,
			In\GroupJoined::class,
			In\GroupJoined::class,
			In\GroupJoined::class,
		];
		foreach ($classes as $class) {
			$this->assertInstanceOf($class, $parser->parseBinaryPackage($connection->read()));
		}
	}
}
