<?php
namespace lib\helper;

use DateInterval;
use DateTime;
use Exception;

/**
 * The DateHelper
 * Utility class with date functions
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
readonly class DateHelper {

	/**
	 * @throws Exception
	 */
	public static function getDifferenz( DateTime $date_1, DateTime $date_2 ): DateInterval {
		if( $date_1 <= $date_2 ) {
			$date_start = $date_1;
			$date_end = $date_2;
		}
		else {
			$date_start = $date_2;
			$date_end = $date_1;
		}
		return $date_start->diff( $date_end );
	}

	/**
	 * @throws Exception
	 */
	public static function getAge( DateTime $date ): int {
		return self::getDifferenz( $date, new DateTime() )->y;
	}

}