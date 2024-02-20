<?php declare(strict_types=1);

namespace AO\Package;

use AO\{BinaryPackage, Package};

abstract class In extends Package {
	public function toBinary(): BinaryPackage\In {
		$package = parent::toBinary();
		return new BinaryPackage\In(
			type: $package->type,
			length: $package->length,
			body: $package->body,
		);
	}
}
