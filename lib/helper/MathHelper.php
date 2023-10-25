<?php

namespace lib\helper;

readonly class MathHelper {

	/**
	 * Returns the correct rounded currency of a numeric value
	 *
	 * @param int|float|string $value
	 * @return float
	 */
	public static function getRoundedCurrency(int|float|string $value): float {
		if( is_string($value) ) {
			$value = (float)str_replace(",", ".", $value);
		}
		return ceil($value * 100) / 100;
	}


}
