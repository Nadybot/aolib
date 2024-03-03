<?php declare(strict_types=1);

namespace AO;

interface AccountUnfreezer {
	public function unfreeze(): bool;
}
