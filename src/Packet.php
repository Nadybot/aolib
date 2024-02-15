<?php declare(strict_types=1);

namespace AO\Client;

class Packet {
	public function __construct(
		public readonly int $type,
		public readonly int $length,
		public readonly ?string $body=null,
	) {
	}
}