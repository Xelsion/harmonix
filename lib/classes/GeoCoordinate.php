<?php

namespace lib\classes;

class GeoCoordinate {

    public float $longitude;

    public float $latitude;

    public function __construct( float $longitude, float $latitude) {
        $this->longitude = deg2rad($longitude);
        $this->latitude = deg2rad($latitude);
    }

}