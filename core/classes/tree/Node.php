<?php

namespace core\classes\tree;

class Node {

    public int $_id = 0;
    public ?int $_child_of = null;
    public string $_name = "";

    public function __construct( int $id, ?int $child_of, string $name) {
        $this->_id = $id;
        $this->_child_of = $child_of;
        $this->_name = $name;
    }

}