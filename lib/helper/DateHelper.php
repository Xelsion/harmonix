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
	 * Calculates the time between two dates
	 *
	 * @param DateTime $date_1
	 * @param DateTime $date_2
	 * @return DateInterval
	 */
	public static function getTimeBetween(DateTime $date_1, DateTime $date_2): DateInterval {
		if( $date_1 <= $date_2 ) {
			$date_start = $date_1;
			$date_end = $date_2;
		} else {
			$date_start = $date_2;
			$date_end = $date_1;
		}
		return $date_start->diff($date_end);
	}

	/**
	 * Returns the difference in years between now and the given DateTime
	 * @throws Exception
	 */
	public static function getAge(DateTime $date): int {
		return self::getTimeBetween($date, new DateTime())->y;
	}

	/**
	 * Returns a formatted string representing the given DateInterval
	 *
	 * @param DateInterval $date
	 * @return string
	 */
	public static function getFormattedTimespan(DateInterval $date): string {
		$y = ($date->y === 1) ? "Jahr" : "Jahre";
		$m = ($date->m === 1) ? "Monat" : "Monate";
		$d = ($date->d === 1) ? "Tag" : "Tage";
		$output = $date->y . " " . $y;
		$output .= " " . (($date->m < 10) ? "0" . $date->m : $date->m) . " " . $m;
		$output .= " " . (($date->d < 10) ? "0" . $date->d : $date->d) . " " . $d;
		return $output;
	}

}