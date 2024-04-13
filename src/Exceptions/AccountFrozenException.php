<?php declare(strict_types=1);

namespace AO\Exceptions;

use AO\FrozenAccount;

class AccountFrozenException extends LoginException {
	public function __construct(
		private readonly FrozenAccount $account,
	) {
		parent::__construct(message: 'Your account is currently frozen');
	}

	public function getAccount(): FrozenAccount {
		return $this->account;
	}
}
