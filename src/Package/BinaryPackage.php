<?php declare(strict_types=1);

namespace AO\Package;

use function Safe\{pack, unpack};
use Stringable;

class BinaryPackage implements Stringable {
	final public function __construct(
		public readonly Type $type,
		public readonly int $length,
		public readonly string $body="",
	) {
	}

	public function __toString(): string {
		$classes = explode("\\", get_class($this));
		$class = array_pop($classes);
		$binData = join("", array_map(dechex(...), array_map(ord(...), str_split($this->body, 1))));
		if (strlen($binData)) {
			$binData = "0x{$binData}";
		} else {
			$binData = '""';
		}
		return "<{$class}>{".
				"type={$this->type->name},".
				"length={$this->length},".
				"body={$binData}}";
	}

	public function toBinary(): string {
		return pack("nn", $this->type->value, $this->length) . ($this->body??"");
	}

	public static function fromBinary(string $binary): self {
		$header = unpack("ntype/nlength", $binary);
		assert(is_int($header['length']));
		assert($header['length'] >= 0);
		$binBody = substr($binary, 4);
		return new static(
			type: Type::from($header['type']),
			length: $header['length'],
			body: $binBody,
		);
	}
}
