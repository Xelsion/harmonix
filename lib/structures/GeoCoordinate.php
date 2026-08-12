<?php

namespace lib\structures;

class GeoCoordinate {

	public float $longitude;

	public float $latitude;

	public float $altitude;

	public function __construct(float $longitude, float $latitude, float $altitude = 0.0) {
		$this->longitude = $longitude;
		$this->latitude = $latitude;
		$this->altitude = $altitude;
	}

}