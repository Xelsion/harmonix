<?php

namespace lib\core\enums;

enum QueryType: int {
	case SELECT = 1;
	case INSERT = 2;
	case UPDATE = 3;
	case DELETE = 4;
	case TRUNCATE = 5;

	/**
	 * Returns a string representing the
	 *
	 * @return string
	 */
	public function toString(): string {
		return match ($this) {
			self::SELECT => "SELECT",
			self::INSERT => "INSERT",
			self::UPDATE => "UPDATE",
			self::DELETE => "DELETE",
			self::TRUNCATE => "TRUNCATE"
		};
	}

}