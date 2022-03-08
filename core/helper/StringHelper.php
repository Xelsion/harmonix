<?php

namespace core\helper;

use core\classes\Configuration;

/**
 * Functions that helps with strings
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class StringHelper {

	private static string $enc_key = 'Q#?9Q=M-&m$@o>>7\ZC$:~?:oRx%@uubnH>YrNwLjt,ieoLK;Mw%,xn2NPhs*c2<>SZQV&NbQA5W_vN;p=UVVd^vHWK&e`;xp9Mpr`azgvUXPph~Zd*2Eh/zx-5,dMmm';
	private static string $enc_iv = '5657372598585078';

	/**
	 * Shortens the given string to the given length
	 * and returns the string.
	 * if np_word_brake is true it will keep all words intact
	 * and don't break them
	 *
	 * @param string $string
	 * @param int $length
	 * @param bool $no_word_break
	 * @return string
	 */
	public static function getShortString( string $string, int $length, bool $no_word_break = false ): string {
		$result = $string;
		if( strlen($string) > $length ) {
			$result = "";
			if( $no_word_break ) {
				$temp = "";
				$words = explode(" ", $string);
				while( !empty($words) ) {
					$word = array_shift($words);
					if( $temp === "" ) {
						$temp .= $word;
					} else {
						$temp .= " ".$word;
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
		}
		return $result;
	}

	/**
	 * Returns a BCrypt string from the given string
	 *
	 * @param string $str
	 * @return string
	 */
	public static function getBCrypt( string $str ): string {
		return password_hash($str, PASSWORD_BCRYPT);
	}

	public static function encrypt( string $string ): string {
		$ciphering = "AES-128-CTR";
		$iv_length = openssl_cipher_iv_length($ciphering);
		$options = 0;
		$encryption_iv = static::$enc_iv;
		$encryption_key = static::$enc_key;
		/** @noinspection EncryptionInitializationVectorRandomnessInspection */
		return openssl_encrypt($string, $ciphering, $encryption_key, $options, $encryption_iv);
	}

	public static function decrypt( string $string ): string {
		$ciphering = "AES-128-CTR";
		$iv_length = openssl_cipher_iv_length($ciphering);
		$options = 0;
		$decryption_iv = static::$enc_iv;
		$decryption_key = static::$enc_key;
		return openssl_decrypt($string, $ciphering, $decryption_key, $options, $decryption_iv);
	}
}