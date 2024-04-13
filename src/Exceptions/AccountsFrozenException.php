<?php declare(strict_types=1);

namespace AO\Exceptions;

use AO\FrozenAccount;

class AccountsFrozenException extends LoginException {
	/** @param FrozenAccount[] $accounts */
	public function __construct(
		private readonly array $accounts,
	) {
		parent::__construct(message: 'One or more accounts are currently frozen');
	}

	/** @return FrozenAccount[] */
	public function getAccounts(): array {
		return $this->accounts;
	}
}
