<?php declare(strict_types=1);

namespace AO\Package;

use AO\{BinaryPackage, Package};

abstract class OutPackage extends Package {
	public function toBinaryPackage(): BinaryPackage\BinaryPackageOut {
		$package = parent::toBinaryPackage();
		return new BinaryPackage\BinaryPackageOut(
			type: $package->type,
			length: $package->length,
			body: $package->body,
		);
	}
}
