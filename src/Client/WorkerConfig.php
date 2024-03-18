<?php declare(strict_types=1);

namespace AO\Client;

use InvalidArgumentException;
use Stringable;

class WorkerConfig implements Stringable {
	public function __construct(
		public readonly int $dimension,
		public readonly string $username,
		public readonly string $password,
		public readonly string $character,
		public readonly ?string $unfreezeLogin=null,
		public readonly ?string $unfreezePassword=null,
	) {
	}

	public function __toString(): string {
		$classes = explode("\\", get_class($this));
		$class = array_pop($classes);
		return "<{$class}>{".
				"dimension={$this->dimension},".
				"username={$this->username},".
				"password=******,".
				"character={$this->character}}";
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
