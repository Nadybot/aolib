<?php declare(strict_types=1);

namespace AO\Group;

use function Safe\{pack, unpack};
use Stringable;

class GroupId implements Stringable {
	public GroupType $type;

	public function __construct(
		int|GroupType $type,
		public int $number,
	) {
		$this->type = is_int($type) ? GroupType::from($type) : $type;
	}

	/** {@inheritDoc} */
	public function __toString(): string {
		return "<GroupId>{type={$this->type->name},number={$this->number}}";
	}

	/** Convert into the binary representation */
	public function toBinary(): string {
		return pack('CN', $this->type->value, $this->number);
	}

	/** Parse a new instance from a binary representation */
	public static function fromBinary(string $binary): self {
		/** @var array{"type":int,"number":int} */
		$data = unpack('Ctype/Nnumber', $binary);
		return new self(
			type: GroupType::from($data['type']),
			number: $data['number'],
		);
	}

	/** Check if the given GroupId is the same as this one */
	public function sameAs(self $other): bool {
		return $this->type === $other->type && $this->number === $other->number;
	}
}
