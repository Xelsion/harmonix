<?php

namespace lib\helper;

use lib\App;

class ValidationHelper {
	private const regex_street = '/^[a-zA-Z|ÖÜÄöäüß]+([\s|-]?[A-Za-z|ÖÜÄöäüß]+)*\.?$/u';
	private const regex_street_nr = '/^\d+[a-z]?\s?(-\s?\d+[a-z]?)?$/';
	private const regex_zipcode = '/^\d{5}$/';
	private const regex_city = '/^[a-zA-Z|ÖÄÜöäüß]+([\s|-]?[A-Za-z|ÖÜÄöäüß]+)*$/u';

	public static function isValidEmail(string $value): bool {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public static function isValidNumeric(string $value): bool {
		return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}

	public static function isValidInteger(string $value): bool {
		return filter_var($value, FILTER_VALIDATE_INT);
	}

	public static function isValidStreet(string $value): bool {
		return (bool)preg_match(self::regex_street, $value);
	}

	public static function isValidStreetNr(string $value): bool {
		return (bool)preg_match(self::regex_street_nr, $value);
	}

	public static function isValidZipcode(string $value): bool {
		return (bool)preg_match(self::regex_zipcode, $value);
	}

	public static function isValidCity(string $value): bool {
		return (bool)preg_match(self::regex_city, $value);
	}

	public static function isValidPassword(string $value): bool {
		$password_config = App::$config->getSection("passwords");
		if( isset($password_config['min_uppercase']) ) {
			$min_uppers = (int)$password_config['min_uppercase'];
			if( !self::hasAmountOfValues("/([A-Z|ÖÜÄ])/u", $value, $min_uppers) ) {
				return false;
			}
		}

		if( isset($password_config['min_numbers']) ) {
			$min_numbers = (int)$password_config['min_numbers'];
			if( !self::hasAmountOfValues("/(\d)/u", $value, $min_numbers) ) {
				return false;
			}
		}

		if( isset($password_config['min_special_chars']) ) {
			$min_special_chars = (int)$password_config['min_special_chars'];
			if( !self::hasAmountOfValues("/([!|§$%&@\/()\[\]*#+\-])/u", $value, $min_special_chars) ) {
				return false;
			}
		}

		if( isset($password_config['min_length']) ) {
			$min_length = (int)$password_config['min_length'];
			if( strlen($value) < $min_length ) {
				return false;
			}
		}
		return true;
	}

	private static function hasAmountOfValues(string $pattern, string $value, int $amount): bool {
		$matches = array();
		preg_match_all($pattern, $value, $matches);
		$num_matches = count($matches[1]);
		return $num_matches >= $amount;
	}
}