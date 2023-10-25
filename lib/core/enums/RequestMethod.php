<?php

namespace lib\core\enums;

enum RequestMethod: int {
	case ANY = 0;
	case GET = 1;
	case POST = 2;
	case PUT = 3;
	case DELETE = 4;

	public function toString(): string {
		return match ($this) {
			self::ANY => "ANY",
			self::GET => "GET",
			self::POST => "POST",
			self::PUT => "PUT",
			self::DELETE => "DELETE"
		};
	}

	public function toInputString(): string {
		$input_template = '<input type="hidden" name="request_method" value="%s">';
		return match ($this) {
			self::ANY => sprintf($input_template, "ANY"),
			self::GET => sprintf($input_template, "GET"),
			self::POST => sprintf($input_template, "POST"),
			self::PUT => sprintf($input_template, "PUT"),
			self::DELETE => sprintf($input_template, "DELETE")
		};
	}

}