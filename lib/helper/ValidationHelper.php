<?php

namespace lib\helper;

use lib\App;

class ValidationHelper {

	private const regex_street = '/^\d*[a-zA-Z|ÖÜÄöäüß]+([\s|-]?[A-Za-z|ÖÜÄöäüß]+)*\.?$/u';
	private const regex_street_nr = '/^\d+[a-z]?\s?(-\s?\d+[a-z]?)?$/';
	private const regex_street_complete = '/^\d*[a-zA-Z|ÖÜÄöäüß]+([\s|-]?[A-Za-z|ÖÜÄöäüß]+)*\.?\s\d+[a-z]?\s?(-\s?\d+[a-z]?)?$/u';
	private const regex_zipcode = '/^\d{5}$/';
	private const regex_city = '/^[a-zA-Z|ÖÄÜöäüß]+([\s|-]?[A-Za-z|ÖÜÄöäüß]+)*$/u';
	private const regex_phone_county_code = '/^\+\d{1,3}$/';
	private const regex_phone_city_code = '/^(\(\d{2,5}\)|-?\d{2,5})$/';
	private const regex_phone_number = '/^-?\d{3,9}(-\d{1,5})?$/';
	private const regex_phone_complete = '/^\+\d{1,3}\s?(\(\d{2,5}\)|-?\d{2,5})\s?-?\d{3,9}(-\d{1,5})?$/';
	private const regex_mobil_prefix = '/^\d{3,5}$/';
	private const regex_mobil_number = '/^\d{3,9}$/';
	private const regex_mobil_complete = '/^\d{3,5}\s\d{3,9}$/';

	public static function isValidEmail(string $value): bool {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public static function isValidNumeric(string $value): bool {
		return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}

	public static function isValidInteger(string $value): bool {
		return filter_var($value, FILTER_VALIDATE_INT);
	}

	public static function isValidBoolean(string $value): bool {
		return filter_var($value, FILTER_VALIDATE_BOOL);
	}

	public static function isValidStreetComplete(string $value): bool {
		return (bool)preg_match(self::regex_street_complete, $value);
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

	// don't work because this would exceed max_int_value :(
	public static function isValidIban(string $value): bool {
		$value = strtoupper($value);
		$value = str_replace([" ", "-"], "", $value);
		$iban_checksum = substr($value, 0, 4);
		$iban_value = substr($value, 4);
		$iban_swapped = $iban_value . $iban_checksum;
		$chars = str_split($iban_swapped);
		$as_numbers = "";

		foreach( $chars as $char ) {
			$as_numbers .= (preg_match("/[A-Z]/", $char)) ? ord($char) - 55 : $char;
		}
		$number = (int)$as_numbers;
		return (($number % 97) === 1);
	}

	public static function isValidPhoneCountryCode(string $value): bool {
		return (bool)preg_match(self::regex_phone_county_code, $value);
	}

	public static function isValidPhoneCityCode(string $value): bool {
		return (bool)preg_match(self::regex_phone_city_code, $value);
	}

	public static function isValidPhoneNumber(string $value): bool {
		return (bool)preg_match(self::regex_phone_number, $value);
	}

	public static function isValidPhoneComplete(string $value): bool {
		return (bool)preg_match(self::regex_phone_complete, $value);
	}

	public static function isValidMobilPrefix(string $value): bool {
		return (bool)preg_match(self::regex_mobil_prefix, $value);
	}

	public static function isValidMobilNumber(string $value): bool {
		return (bool)preg_match(self::regex_mobil_number, $value);
	}

	public static function isValidMobilComplete(string $value): bool {
		return (bool)preg_match(self::regex_mobil_complete, $value);
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