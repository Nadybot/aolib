<?php declare(strict_types=1);

namespace AO\Client;

use InvalidArgumentException;

class WorkerConfig {
	public function __construct(
		public int $dimension,
		public string $username,
		public string $password,
		public string $character,
	) {
	}

	public function getServer(): string {
		return match ($this->dimension) {
			4 => "chat.dt.funcom.com:7109",
			5 => "chat.d1.funcom.com:7105",
			6 => "chat.d1.funcom.com:7106",
			default => throw new InvalidArgumentException("No valid server to connect with! Available dimensions are 4, 5, and 6.")
		};
	}
}
