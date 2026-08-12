<?php

namespace lib\helper;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;

/**
 * Functions that helps with strings
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class StringHelper {

	private static string $allowed_characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_$!%";
	private static string $allowed_password_characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789-_$!%@#&=?";
	private static string $enc_ciphering = "AES-256-GCM";
	private static int $tag_length = 16;
	private static int $enc_option = OPENSSL_RAW_DATA;

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
		if( mb_strlen($string) <= $length ) {
			return $string;
		}

		if( $suffix !== "" ) {
			$length -= mb_strlen($suffix);
		}

		if( $no_word_break ) {
			// Cuts off text and rolls back to the last space character
			$truncated = mb_substr($string, 0, $length + 1);
			$last_space = mb_strrpos($truncated, ' ');
			if( $last_space !== false ) {
				return mb_substr($truncated, 0, $last_space) . $suffix;
			}
		}

		return mb_substr($string, 0, $length) . $suffix;
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
		$length -= (mb_strlen($prefix) + mb_strlen($suffix));
		if( $length <= 0 ) {
			return $prefix . $suffix;
		}

		$index_end = mb_strlen(self::$allowed_characters) - 1;
		$random_string = "";

		for( $i = 0; $i < $length; $i++ ) {
			$random_string .= self::$allowed_characters[random_int(0, $index_end)];
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
		$index_end = mb_strlen(self::$allowed_password_characters) - 1;
		$result = "";
		for( $i = 0; $i < $length; $i++ ) {
			$result .= self::$allowed_password_characters[random_int(0, $index_end)];
		}
		return $result;
	}

	/**
	 * Puts a <span> around the given $needle in the given string and returns the result.
	 * Secure against XSS.
	 *
	 * @param string $needle
	 * @param string $string
	 * @param string $tag default "span"
	 * @param string $class default "hl"
	 *
	 * @return string
	 */
	public static function getHighlighted(string $needle, string $string, string $tag = "span", string $class = "hl"): string {
		if( trim($needle) === "" ) {
			return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
		}
		$safe_string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
		$safe_needle = htmlspecialchars($needle, ENT_QUOTES, 'UTF-8');

		$quoted_needle = preg_quote($safe_needle, '/');

		// Replaces all matches case-insensitively and properly wraps them in HTML tags
		return preg_replace_callback('/' . $quoted_needle . '/i', static function($matches) use ($tag, $class) {
			return sprintf('<%s class="%s">%s</%s>', $tag, $class, $matches[0], $tag);
		}, $safe_string);
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
	 * @throws SystemException
	 */
	public static function getGuID(bool $trim = true): string {
		try {
			// Windows
			if( function_exists('com_create_guid') === true && is_callable('com_create_guid') ) {
				if( $trim === true ) {
					return trim(com_create_guid(), '{}');
				}
				return com_create_guid();
			}

			// OSX/Linux
			if( function_exists('random_bytes') === true && is_callable('random_bytes') ) {
				$data = random_bytes(16);
				if( $data ) {
					$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // setClass version to 0100
					$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // setClass bits 6-7 to 10
					return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
				}
			}

			// No GUID support detected
			throw new SystemException(__FILE__, __LINE__, "No GUID support available!");
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Encrypts a string and returns the encrypted string
	 *
	 * @param string $string
	 * @param string $salt
	 * @return string
	 * @throws SystemException
	 */
	public static function encrypt(string $string, string $salt = ""): string {
		try {
			$base_key = App::$config->getSectionValue('security', 'enc_key');
			$enc_key = hash_hmac('sha256', $salt, $base_key, true);

			$iv_length = openssl_cipher_iv_length(self::$enc_ciphering);
			$iv = random_bytes($iv_length);

			// AES-GCM requires a reference variable for the authentication tag
			$tag = "";

			$ciphertext = openssl_encrypt($string, self::$enc_ciphering, $enc_key, self::$enc_option, $iv, $tag, "", self::$tag_length);

			// Append IV and Authentication Tag directly to the data stream
			return $iv . $tag . $ciphertext;
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}

	}

	/**
	 * Decrypts an encrypted string and returns the result
	 *
	 * @param string $string
	 * @param string $salt
	 * @return string
	 * @throws SystemException
	 */
	public static function decrypt(string $string, string $salt = ""): string {
		try {
			$base_key = App::$config->getSectionValue('security', 'enc_key');
			$enc_key = hash_hmac('sha256', $salt, $base_key, true);

			$iv_length = openssl_cipher_iv_length(self::$enc_ciphering);

			$iv = substr($string, 0, $iv_length);
			$tag = substr($string, $iv_length, self::$tag_length);
			$ciphertext = substr($string, $iv_length + self::$tag_length);

			$plaintext = openssl_decrypt($ciphertext, self::$enc_ciphering, $enc_key, self::$enc_option, $iv, $tag);

			if( $plaintext === false ) {
				throw new SystemException(__FILE__, __LINE__, "Decryption failed or data was tampered with.");
			}

			return $plaintext;
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, "Invalid encrypted payload or authentication signature failed.");
		}
	}


	/**
	 * Returns the current server domain path without the subdomain
	 *
	 * @return string
	 */
	public static function getDomain(): string {
		$rawHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
		if( $rawHost === '' ) {
			return '';
		}

		// Remove port mapping if present (e.g., localhost:8080 -> localhost)
		$host = explode(':', $rawHost)[0];
		$parts = explode('.', $host);
		$count = count($parts);

		// Handle localhost or raw IP addresses
		if( $count === 1 || filter_var($host, FILTER_VALIDATE_IP) ) {
			return $host;
		}

		// Detect multi-segment TLDs (e.g., co.uk, com.de, org.at)
		$two_letter_tlds = ['co', 'com', 'org', 'net', 'gov', 'edu', 'ac'];
		if( $count >= 3 && in_array($parts[$count - 2], $two_letter_tlds, true) ) {
			return '.' . $parts[$count - 3] . '.' . $parts[$count - 2] . '.' . $parts[$count - 1];
		}

		// Standard domain fallback (e.g., www.example.com -> .example.com)
		return '.' . $parts[$count - 2] . '.' . $parts[$count - 1];
	}

	/**
	 * @param string|null $str
	 * @return bool
	 */
	public static function isNullOrEmpty(?string $str): bool {
		return ($str === null || trim($str) === "");
	}

}
