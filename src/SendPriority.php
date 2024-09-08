<?php declare(strict_types=1);

namespace AO;

enum SendPriority: int {
	case High = 1;
	case Medium = 2;
	case Low = 3;
}
