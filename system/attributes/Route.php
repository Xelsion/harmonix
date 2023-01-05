<?php

namespace system\attributes;

#[\Attribute]
class Route {

    public string $path;

    public string $method;

    public function __construct( string $path, string $method = HTTP_GET ) {
        $this->path = $path;
        $this->method = $method;
    }

}