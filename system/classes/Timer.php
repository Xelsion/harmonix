<?php

namespace system\classes;

/**
 * The Time class
 * Can be used to measure time
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Timer {

    private float $_start;
    private float $_elapsed_time = 0.0;

    /**
     * The class constructor
     */
    public function __construct() {

    }

    /**
     * Sets the starting moment
     *
     * @return void
     */
    public function start(): void {
        $this->_start = microtime(TRUE);
    }

    /**
     * Sets the elapsed time between now and the starting moments
     *
     * @return void
     */
    public function stop(): void {
        $this->_elapsed_time += microtime(TRUE) - $this->_start;
    }

    /**
     * Returns the elapsed time in sec
     *
     * @return float
     */
    public function getElapsedTime(): float {
        return $this->_elapsed_time;
    }

}
