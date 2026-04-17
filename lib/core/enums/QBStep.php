<?php

namespace lib\core\enums;

enum QBStep: int {
	case GROUP = -2;
	case SUBQUERY = -1;
	case NONE = 0;
	case START = 1; // Select|Insert|Update|Delete|Truncate
	case VALUES = 2; // Values|Set values
	case FROM = 3;
	case JOIN = 4; // Join|Join Left|Join Right
	case JOIN_ON = 5;
	case AS = 6;
	case WHERE = 7;
	case WHERE_ADDS = 8; // And|Or
	case GROUP_BY = 9;
	case HAVING = 10;
	case ORDER_BY = 11;
	case LIMIT = 12;
	case RETURNING = 13;
}