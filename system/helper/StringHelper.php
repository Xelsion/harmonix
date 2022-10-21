<?php

namespace system\helper;

use Exception;

/**
 * Functions that helps with strings
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class StringHelper {

	private static string $_allowed_characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_$!%";
	private static string $_allowed_password_characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789-_$!%@#&=?";

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
	 * Creates a random string with the given $length.
	 * Default length is 16
	 *
	 * @param int $length
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function getRandomString( int $length = 16 ): string {
		$index_start = 0;
		$index_end = strlen(self::$_allowed_characters) - 1;
		$result = "";
		for( $i = 0; $i < $length; $i++ ) {
			$random_index = random_int($index_start, $index_end);
			$result .= self::$_allowed_characters[$random_index];
		}
		return $result;
    }

	/**
	 * Creates random password string with the given $length.
	 * Default $length is 8
	 *
	 * @param int $length
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function getRandomPassword( int $length = 8 ): string {
		$index_start = 0;
		$index_end = strlen(self::$_allowed_password_characters) - 1;
		$result = "";
		for( $i = 0; $i < $length; $i++ ) {
			$random_index = random_int($index_start, $index_end);
			$result .= self::$_allowed_password_characters[$random_index];
		}
		return $result;
	}

    public static function getHighlighted( string $hl_part, string $string ): string {
        $matches = array();
        preg_match("/".$hl_part."/i", $string, $matches);
        if( empty($matches) ) {
            return $string;
        }
        foreach( $matches as $match ) {
            $string = preg_replace('/'.$match.'/', '<span class="hl">'.$match.'</span>', $string);
        }
        return $string;
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
