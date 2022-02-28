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

	public static function getSecureString( string $string ): string {
		return htmlentities($string, ENT_QUOTES);
	}

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

	public static function getBCrypt( string $str ): string {
		return password_hash($str, PASSWORD_BCRYPT);
	}
}