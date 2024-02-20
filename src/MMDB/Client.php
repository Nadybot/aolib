<?php declare(strict_types=1);

namespace AO\MMDB;

/**
 * A generic interface for an MMDB client that allows reading predefined strings
 */
interface Client {
	public function getMessageString(int $categoryId, int $messageId): ?string;
}
