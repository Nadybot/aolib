<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package\{BasePackage, BinaryPackageOut};

class OutPackage extends BasePackage {
	public function toBinary(): BinaryPackageOut {
		$package = parent::toBinary();
		return new BinaryPackageOut(
			type: $package->type,
			length: $package->length,
			body: $package->body,
		);
	}

	protected function getFormat(): string {
		return $this->type->formatOut();
	}
}
