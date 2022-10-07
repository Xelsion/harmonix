<?php

namespace system\classes;

class Timer {

    private float $_start;
    private float $_elapsed_time = 0.0;

    public function __construct() {

    }

    public function start(): void {
        $this->_start = microtime(TRUE);
    }

    public function stop(): void {
        $this->_elapsed_time += microtime(TRUE) - $this->_start;
    }

    public function getElapsedTime(): float {
        return $this->_elapsed_time;
    }

}
