<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\Type;

class ClientLookupResult extends InPackage {
	public function __construct(
		public int $uid,
		public string $name,
	) {
		parent::__construct(Type::CLIENT_LOOKUP);
	}

	public function getUid(): ?int {
		return $this->uid === 0xFFFFFFFF ? null : $this->uid;
	}
}
