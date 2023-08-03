<?php

namespace lib\helper;

class DataHelper {

	/**
	 * @param string $str1
	 * @param string $str2
	 * @param string $direction
	 * @return int
	 */
	public static function stringCompare(string $str1, string $str2, bool $ascending = true): int {
		return ($ascending) ? strcasecmp($str1, $str2) : strcasecmp($str2, $str1);
	}

	/**
	 * @param int|float $num1
	 * @param int|float $num2
	 * @param string $direction
	 * @return int
	 */
	public static function numberCompare(int|float $num1, int|float $num2, bool $ascending = true): int {
		if( $num1 === $num2 ) {
			return 0;
		}
		if( $num1 > $num2 ) {
			return ($ascending) ? 1 : -1;
		}
		return ($ascending) ? -1 : 1;
	}

}