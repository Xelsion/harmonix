<?php

namespace lib\core\enums;

enum Subdomain: int {
	case ANY = 0;
	case WWW = 1;
	case ADMIN = 2;

	/**
	 * @return string
	 */
	public function toString(): string {
		return match ($this) {
			self::ANY => "*",
			self::WWW => "www",
			self::ADMIN => "admin"
		};
	}
}