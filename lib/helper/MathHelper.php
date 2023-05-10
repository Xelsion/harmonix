<?php

namespace lib\helper;

use lib\classes\GeoCoordinate;

class MathHelper {

    public static function getDistanceBetween( GeoCoordinate $coordinate1, GeoCoordinate $coordinate2, $format = "K" ): float {
        $earth_radius = 6378.388;
        $sin_lat = sin($coordinate1->latitude) * sin($coordinate2->latitude);
        $cos_lat = cos($coordinate1->latitude) * cos($coordinate2->latitude);
        $cos_long = cos($coordinate2->longitude - $coordinate1->longitude);
        $distance = $earth_radius * acos( $sin_lat + $cos_lat * $cos_long);
        return match ($format) {
            "M" => $distance / 1.609,
            "N" => $distance / 1.852,
            default => $distance
        };
    }

    /**
     * @param float $distance
     * @return string
     */
    public static function getFormattedDistance( float $distance ): string {
        $current_unit = "km";
        while( $distance < 1 ) {
            $distance *= ( $current_unit === "km") ? 1000 : 100;
            switch( $current_unit ) {
                case "km": $current_unit = "m"; break;
                case "m": $current_unit = "cm"; break;
                case "cm": $current_unit = "mm"; break;
            }
            if( $current_unit ===  "mm" ) {
                break;
            }
        }
        return round($distance,2) . " " . $current_unit;
    }

}
