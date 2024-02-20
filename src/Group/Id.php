<?php declare(strict_types=1);

namespace AO\Group;

use function Safe\pack;

use Stringable;

class Id implements Stringable {
	public Type $type;

	public function __construct(
		int|Type $type,
		public int $number,
	) {
		$this->type = is_int($type) ? Type::from($type) : $type;
	}

	public function __toString(): string {
		return "<GroupId>{type={$this->type->name},number={$this->number}}";
	}

	public function toBinary(): string {
		return pack("CN", $this->type->value, $this->number);
	}

	public function sameAs(self $other): bool {
		return $this->type === $other->type && $this->number === $other->number;
	}
}
