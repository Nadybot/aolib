<?php declare(strict_types=1);

namespace AO\Package\Out;

use AO\Package;
use AO\Package\{Attributes as Attr};

class LoginRequest extends Package\OutPackage {
	public function __construct(
		#[Attr\Param(1)] public string $username,
		#[Attr\Param(2)] public string $key,
		#[Attr\Param(0)] public int $zero=0,
	) {
		parent::__construct(Package\PackageType::LoginRequest);
	}

	public static function getFormat(): string {
		return 'ISS';
	}
}
