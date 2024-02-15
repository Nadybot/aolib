<?php declare(strict_types=1);

namespace AO\Package;

final class ExtendedMessage {
	public function __construct(
		public readonly string $rawMessage,
		/** @var mixed[] */
		public readonly array $args,
		public readonly int $category,
		public readonly int $instance,
		public readonly ?string $messageString,
		public readonly string $message,
	) {
	}
}
