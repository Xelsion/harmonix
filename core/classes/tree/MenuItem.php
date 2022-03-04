<?php

namespace core\classes\tree;

class MenuItem extends Node {

    public string $_target = "";

    public function __construct( int $id, ?int $child_of, string $name, string $target ) {
        parent::__construct( $id, $child_of, $name);
        $this->_target = $target;
    }

    public function getLink( string $class = "") {
        return sprintf('<a href="%s" class="%s">%s</a>', $this->_target, $class, $this->_name);
    }

}