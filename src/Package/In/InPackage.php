<?php declare(strict_types=1);

namespace AO\Package\In;

use AO\Package\{BasePackage, BinaryPackageIn};

abstract class InPackage extends BasePackage {
	public function toBinary(): BinaryPackageIn {
		$package = parent::toBinary();
		return new BinaryPackageIn(
			type: $package->type,
			length: $package->length,
			body: $package->body,
		);
	}
}
