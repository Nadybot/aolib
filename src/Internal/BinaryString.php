<?php declare(strict_types=1);

namespace AO\Internal;

use Stringable;

final class BinaryString implements Stringable {
	public function __construct(private string $data) {
	}

	public function __toString(): string {
		$binData = implode(
			'',
			array_map(
				dechex(...),
				array_map(
					ord(...),
					str_split($this->data, 1)
				)
			)
		);
		if (strlen($binData)) {
			return "0x{$binData}";
		}
		return '""';
	}
}
