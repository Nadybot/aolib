<?php declare(strict_types=1);

namespace AO\Package;

use AO\{BinaryPackage, Package};

abstract class Out extends Package {
	public function toBinaryPackage(): BinaryPackage\Out {
		$package = parent::toBinaryPackage();
		return new BinaryPackage\Out(
			type: $package->type,
			length: $package->length,
			body: $package->body,
		);
	}
}
