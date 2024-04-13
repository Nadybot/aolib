<?php declare(strict_types=1);

namespace AO\Package;

use AO\{BinaryPackage, Package};

abstract class InPackage extends Package {
	public function toBinaryPackage(): BinaryPackage\BinaryPackageIn {
		$package = parent::toBinaryPackage();
		return new BinaryPackage\BinaryPackageIn(
			type: $package->type,
			length: $package->length,
			body: $package->body,
		);
	}
}
