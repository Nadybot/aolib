<?php declare(strict_types=1);

namespace AO;

class FrozenAccount {
	public function __construct(
		public readonly string $username,
		public readonly ?int $subscriptionId=null,
	) {
	}
}
