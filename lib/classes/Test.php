<?php

namespace lib\classes;

class Test {



    public function __construct( private A $a ) {
        $this->doSomething();
    }

    public function doSomething() {
        echo $this->a->getLetter();
    }

}

class A {

    public function getLetter(): string {
        return 'A';
    }

}