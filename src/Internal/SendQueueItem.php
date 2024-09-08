<?php declare(strict_types=1);

namespace AO\Internal;

use Amp\DeferredFuture;
use AO\Package\OutPackage;

final class SendQueueItem {
	/** @param DeferredFuture<void> $future */
	public function __construct(
		public readonly ?DeferredFuture $future,
		public readonly OutPackage $package,
	) {
	}
}
