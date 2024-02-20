<?php declare(strict_types=1);

namespace AO\Package;

use AO\{BinaryPackage, Package};

abstract class Out extends Package {
	public function toBinary(): BinaryPackage\Out {
		$package = parent::toBinary();
		return new BinaryPackage\Out(
			type: $package->type,
			length: $package->length,
			body: $package->body,
		);
	}
}
