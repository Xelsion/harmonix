<?php

namespace lib\core\enums;

enum QBStep: int {
	case NONE = 0;
	case START = 1;      // Select/Insert/Update/Delete/Truncate
	case VALUES = 2;      // Insert/Update values
	case FROM = 3;
	case JOIN = 4;
	case WHERE = 5;
	case WHERE_ADDS = 6; // And/Or
	case GROUP_BY = 7;
	case HAVING = 8;
	case ORDER_BY = 9;
	case LIMIT = 10;
}