<?php

namespace lib\core\enums;

enum ErrorType: int {

	case WARNING = 1;
	case ERROR = 2;
	case CRITICAL = 3;

	/**
	 * Return the equivalent string
	 *
	 * @return string
	 */
	public function toString(): string {
		return match ($this) {
			self::WARNING => "Warning",
			self::ERROR => "Error",
			self::CRITICAL => "Critical"
		};
	}

}
