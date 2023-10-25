<?php

namespace lib\helper;

use lib\classes\GeoCoordinate;

/**
 * The GeoHelper
 * Utility class with date functions
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
readonly class GeoHelper {

	/**
	 * Calculates the distance between two geo-coordinates in kilometers
	 *
	 * @param GeoCoordinate $coordinate1
	 * @param GeoCoordinate $coordinate2
	 * @param string $formate
	 * @return float
	 */
	public static function getDistanceBetween(GeoCoordinate $coordinate1, GeoCoordinate $coordinate2, string $formate = "K"): float {
		$earth_radius = 6378.388;
		$sin_lat = sin($coordinate1->latitude) * sin($coordinate2->latitude);
		$cos_lat = cos($coordinate1->latitude) * cos($coordinate2->latitude);
		$cos_long = cos($coordinate2->longitude - $coordinate1->longitude);
		$distance = $earth_radius * acos($sin_lat + ($cos_lat * $cos_long));
		return match ($formate) {
			"M" => $distance / 1.609, // Miles
			"N" => $distance / 1.852, // Nautical miles
			default => $distance // Kilometers
		};
	}

	/**
	 * Formates a numeric value to a number grater than 1 and adds the correct measurement to the value
	 *
	 * @param float $distance
	 * @return string
	 */
	public static function getFormattedDistance(float $distance): string {
		$current_unit = "km";
		while( $distance < 1 ) {
			$distance *= ($current_unit === "km") ? 1000 : 100;
			switch( $current_unit ) {
				case "km":
					$current_unit = "m";
					break;
				case "m":
					$current_unit = "cm";
					break;
				case "cm":
					$current_unit = "mm";
					break;
			}
			if( $current_unit === "mm" ) {
				break;
			}
		}
		return round($distance, 2) . " " . $current_unit;
	}

}