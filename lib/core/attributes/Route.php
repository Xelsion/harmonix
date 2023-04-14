<?php
namespace lib\core\attributes;

#[\Attribute]
class Route {

    public string $path;

    public string $method;

    public function __construct( string $path, string $method = "ALL") {
        $this->path = $path;
        $this->method = $method;
    }

}