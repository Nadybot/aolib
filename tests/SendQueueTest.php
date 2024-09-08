<?php declare(strict_types=1);

namespace AO\Tests;

use AO\Internal\SendQueue;
use AO\Package\Out\Pong;
use AO\{SendPriority};
use PHPUnit\Framework\Attributes\{Small};
use PHPUnit\Framework\TestCase;

#[Small]
class SendQueueTest extends TestCase {
	public function testCorrectOrder(): void {
		$queue = new SendQueue();
		$queue->push(new Pong('1'), SendPriority::Low);
		$queue->push(new Pong('2'), SendPriority::High);
		$queue->push(new Pong('3'), SendPriority::Medium);
		$queue->push(new Pong('4'), SendPriority::High);
		$queue->push(new Pong('5'), SendPriority::Low);
		$queue->push(new Pong('6'), SendPriority::Medium);

		foreach (['2', '4', '3', '6', '1', '5'] as $prio) {
			$next = $queue->shift();
			$this->assertNotNull($next);

			/** @var Pong */
			$package = $next->package;
			$this->assertInstanceOf(Pong::class, $package, 'Wrong package dequeued');

			$this->assertSame($package->extra, $prio, 'The order of priority is not uphold');
		}

		$next = $queue->shift();
		$this->assertNull($next);
	}
}
