<?php declare(strict_types=1);

namespace AO\Tests;

use AO\Group\{GroupId, GroupType};
use PHPUnit\Framework\Attributes\{Small};
use PHPUnit\Framework\TestCase;

#[Small]
final class GroupIdTest extends TestCase {
	public function testParsing(): void {
		$group = new GroupId(
			type: GroupType::PVP,
			number: 2_000,
		);
		$binary = $group->toBinary();
		$parsed = GroupId::fromBinary($binary);
		$this->assertTrue($parsed->sameAs($group));
		$this->assertEqualsCanonicalizing($group, $parsed);
	}
}
