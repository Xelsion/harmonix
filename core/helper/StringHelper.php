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
}