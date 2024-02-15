<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\{BasePackage, BinaryPackageIn};

class InPackage extends BasePackage {
	public function toBinary(): BinaryPackageIn {
		$package = parent::toBinary();
		return new BinaryPackageIn(
			type: $package->type,
			length: $package->length,
			body: $package->body,
		);
	}

	protected function getFormat(): string {
		return $this->type->formatIn();
	}
}
