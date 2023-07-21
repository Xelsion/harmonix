<?php

namespace lib\classes;

class GeoCoordinate {

    public float $longitude;

    public float $latitude;

    public float $altitude;

    public function __construct(float $longitude, float $latitude, float $altitude = 0.0) {
        $this->longitude = deg2rad($longitude);
        $this->latitude = deg2rad($latitude);
        $this->altitude = $altitude;
    }

}