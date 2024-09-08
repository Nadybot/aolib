<?php declare(strict_types=1);

namespace AO\Internal;

use Amp\{DeferredFuture};
use AO\Package\OutPackage;
use AO\SendPriority;

final class SendQueue {
	/**
	 * @var array<int,SendQueueItem[]>
	 *
	 * @psalm-var array<int,list<SendQueueItem>>
	 */
	private array $queue = [];

	/** @param DeferredFuture<void> $future */
	public function push(OutPackage $package, SendPriority $priority, ?DeferredFuture $future=null): void {
		if (!isset($this->queue[$priority->value])) {
			$this->queue[$priority->value] = [];
		}
		$this->queue[$priority->value] []= new SendQueueItem(
			future: $future,
			package: $package,
		);
	}

	public function shift(): ?SendQueueItem {
		$priorities = SendPriority::cases();
		asort($priorities);
		foreach ($priorities as $priority) {
			if (isset($this->queue[$priority->value]) && count($this->queue[$priority->value]) > 0) {
				return array_shift($this->queue[$priority->value]);
			}
		}
		return null;
	}

	/** Get the number of total items currently in the queue */
	public function getSize(): int {
		$priorities = SendPriority::cases();
		$size = 0;
		foreach ($priorities as $priority) {
			if (!isset($this->queue[$priority->value])) {
				continue;
			}
			$size += count($this->queue[$priority->value]);
		}
		return $size;
	}
}
