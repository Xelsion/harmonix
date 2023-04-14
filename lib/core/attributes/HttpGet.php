<?php

namespace lib\core\attributes;

#[\Attribute]
class HttpGet extends Route {

    public function __construct( string $path ) {
        parent::__construct( $path, "GET" );
    }

}