<?php

namespace lib\helper;

use Exception;

/**
 * Functions that helps with strings
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class StringHelper {

	private static string $allowed_characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_$!%";
	private static string $allowed_password_characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789-_$!%@#&=?";
	private static string $enc_key = 'Q#?9Q=M-&m$@o>>7\ZC$:~?:oRx%@uubnH>YrNwLjt,ieoLK;Mw%,xn2NPhs*c2<>SZQV&NbQA5W_vN;p=UVVd^vHWK&e`;xp9Mpr`azgvUXPph~Zd*2Eh/zx-5,dMmm';
	private static string $enc_ciphering = "AES-128-CTR";
	private static string $enc_iv = '5657372598585078';

	/**
	 * Shortens the given string to the given length
	 * and returns the string.
	 * if np_word_brake is true it will keep all words intact
	 * and don't break them
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $suffix default ""
	 * @param bool $no_word_break default false
	 *
	 * @return string
	 */
	public static function getShortString(string $string, int $length, string $suffix = "", bool $no_word_break = false): string {
		$result = $string;
		if( strlen($string) > $length ) {
			if( $suffix !== "" ) {
				$length -= strlen($suffix);
			}
			$result = "";
			if( $no_word_break ) {
				$temp = "";
				$words = explode(" ", $string);
				while( !empty($words) ) {
					$word = array_shift($words);
					if( $temp === "" ) {
						$temp .= $word;
					} else {
						$temp .= " " . $word;
					}
					if( strlen($temp) < $length ) {
						$result = $temp;
						continue;
					}
					break;
				}
			} else {
				$result = substr($string, 0, $length - 1);
			}
			$result .= $suffix;
		}
		return $result;
	}

	/**
	 * Creates a random string with the given $length.
	 * Default length is 16
	 *
	 * @param int $length
	 * @param string $prefix default ""
	 * @param string $suffix default ""
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function getRandomString(int $length = 16, string $prefix = "", string $suffix = ""): string {
		$index_start = 0;
		$index_end = strlen(self::$allowed_characters) - 1;
		$random_string = "";
		if( $prefix !== "" ) {
			$length -= strlen($prefix);
		}
		if( $suffix !== "" ) {
			$length -= strlen($suffix);
		}
		for( $i = 0; $i < $length; $i++ ) {
			$random_index = random_int($index_start, $index_end);
			$random_string .= self::$allowed_characters[$random_index];
		}
		return $prefix . $random_string . $suffix;
	}

	/**
	 * Creates random password string with the given $length.
	 * Default $length is 8
	 *
	 * @param int $length default 8
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function getRandomPassword(int $length = 8): string {
		$index_start = 0;
		$index_end = strlen(self::$allowed_password_characters) - 1;
		$result = "";
		for( $i = 0; $i < $length; $i++ ) {
			$random_index = random_int($index_start, $index_end);
			$result .= self::$allowed_password_characters[$random_index];
		}
		return $result;
	}

	/**
	 * Puts a <span> around the given $needle in the given string and returns the
	 * result
	 *
	 * @param string $needle
	 * @param string $string
	 * @param string $tag default "span"
	 * @param string $class default "hl"
	 *
	 * @return string
	 */
	public static function getHighlighted(string $needle, string $string, string $tag = "span", string $class = "hl"): string {
		$matches = array();
		preg_match("/" . $needle . "/i", $string, $matches);
		if( empty($matches) ) {
			return $string;
		}
		foreach( $matches as $match ) {
			$html_replacement = sprintf('<%s class="%s">%s</%s>', $tag, $class, $match, $tag);
			$string = preg_replace('/' . $match . '/', $html_replacement, $string);
		}
		return $string;
	}

	/**
	 * Returns a BCrypt string from the given string
	 *
	 * @param string $str
	 * @return string
	 */
	public static function getBCrypt(string $str): string {
		return password_hash($str, PASSWORD_BCRYPT);
	}

	/**
	 * Generates a GUIDv4
	 *
	 * @param bool $trim default true
	 *
	 * @return string
	 */
	public static function getGuID(bool $trim = true): string {
		// Windows
		if( function_exists('com_create_guid') === true ) {
			if( $trim === true ) {
				return trim(com_create_guid(), '{}');
			}
			return com_create_guid();
		}

		// OSX/Linux
		if( function_exists('openssl_random_pseudo_bytes') === true ) {
			$secured = true;
			$data = openssl_random_pseudo_bytes(16, $secured);
			if( $data ) {
				$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // setClass version to 0100
				$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // setClass bits 6-7 to 10
				return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
			}

		}

		// Fallback (PHP 4.2+)
		mt_srand((double)microtime() * 10000);
		$char_id = strtolower(md5(uniqid(mt_rand(), true)));
		$hyphen = chr(45);                  // "-"
		$lbrace = $trim ? "" : chr(123);    // "{"
		$rbrace = $trim ? "" : chr(125);    // "}"
		return $lbrace . substr($char_id, 0, 8) . $hyphen . substr($char_id, 8, 4) . $hyphen . substr($char_id, 12, 4) . $hyphen . substr($char_id, 16, 4) . $hyphen . substr($char_id, 20, 12) . $rbrace;
	}

	/**
	 * Encrypts a string and returns the encrypted string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function encrypt(string $string): string {
		openssl_cipher_iv_length(self::$enc_ciphering);
		$options = 0;
		/** @noinspection EncryptionInitializationVectorRandomnessInspection */
		return openssl_encrypt($string, self::$enc_ciphering, static::$enc_key, $options, static::$enc_iv);
	}

	/**
	 * Decrypts an encrypted string and returns the result
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function decrypt(string $string): string {
		openssl_cipher_iv_length(self::$enc_ciphering);
		$options = 0;
		return openssl_decrypt($string, self::$enc_ciphering, static::$enc_key, $options, static::$enc_iv);
	}

	/**
	 * Returns the current server domain path without the subdomain
	 *
	 * @return string
	 */
	public static function getDomain(): string {
		$path = $url_parts = parse_url($_SERVER["SERVER_NAME"], PHP_URL_PATH);
		if( $url_parts === false ) {
			return "";
		}
		$matches = array();
		if( preg_match("/^([a-z0-9]+)\.([a-zA-Z0-9]+)\.([a-z-A-Z0-9]+)$/", $path, $matches) ) {
			return "." . $matches[2] . "." . $matches[3];
		}
		if( preg_match("/^([a-zA-Z0-9]+)\.([a-z-A-Z0-9]+)$/", $path, $matches) ) {
			return "." . $matches[1] . "." . $matches[2];
		}
		return "";
	}

	/**
	 * @param string|null $str
	 * @return bool
	 */
	public static function isNullOrEmpty(?string $str): bool {
		return ($str === null || $str === "");
	}

}
