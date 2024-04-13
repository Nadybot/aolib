<?php declare(strict_types=1);

namespace AO\Group;

enum GroupType: int {
	case Org = 3;
	case PVP = 10;
	case Announcements = 12;
	case Shopping = 134;
	case OOC = 135;
}
