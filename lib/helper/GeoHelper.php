<?php

namespace lib\helper;

use lib\structures\GeoCoordinate;

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
	public static function getPlanetaryDistanceBetween(GeoCoordinate $coordinate1, GeoCoordinate $coordinate2, float $radius = 6378.38, string $formate = "K"): float {
		$distance = MathHelper::getGeoDistance($coordinate1->latitude, $coordinate1->longitude, $coordinate2->latitude, $coordinate2->longitude, $radius);
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