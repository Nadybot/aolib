<?php declare(strict_types=1);

namespace AO;

use function Safe\{pack, unpack};

use AO\Internal\BinaryString;
use AO\Package\PackageType;
use Stringable;

class BinaryPackage implements Stringable {
	final public function __construct(
		public readonly PackageType $type,
		public readonly int $length,
		public readonly string $body="",
	) {
	}

	public function __toString(): string {
		$classes = explode("\\", get_class($this));
		$class = array_pop($classes);
		$body = (string)(new BinaryString($this->body));
		return "<{$class}>{".
				"type={$this->type->name},".
				"length={$this->length},".
				"body={$body}}";
	}

	public function toBinary(): string {
		return pack("nn", $this->type->value, $this->length) . $this->body;
	}

	public static function fromBinary(string $binary): self {
		$header = unpack("ntype/nlength", $binary);
		assert(is_int($header['length']));
		assert($header['length'] >= 0);
		$binBody = substr($binary, 4);
		return new static(
			type: PackageType::from($header['type']),
			length: $header['length'],
			body: $binBody,
		);
	}
}
