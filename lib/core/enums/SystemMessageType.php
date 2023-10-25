<?php

namespace lib\core\enums;

enum SystemMessageType: int {

	case SUCCESS = 1;
	case ERROR = 2;
	case WARNING = 3;
	case INFO = 4;

	public function toString(): string {
		return match ($this) {
			self::SUCCESS => "success",
			self::ERROR => "danger",
			self::WARNING => "warning",
			self::INFO => "info"
		};
	}
}
