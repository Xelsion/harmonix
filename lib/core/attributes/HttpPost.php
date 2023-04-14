<?php

namespace lib\core\attributes;

#[\Attribute]
class HttpPost extends Route {

    public function __construct( string $path ) {
        parent::__construct( $path, "POST" );
    }

}