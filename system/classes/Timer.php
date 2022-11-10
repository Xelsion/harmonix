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

    private float $start;
    private float $elapsed_time = 0.0;

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
        $this->start = microtime(TRUE);
    }

    /**
     * Sets the elapsed time between now and the starting moments
     *
     * @return void
     */
    public function stop(): void {
        $this->elapsed_time += microtime(TRUE) - $this->start;
    }

    /**
     * Returns the elapsed time in sec
     *
     * @return float
     */
    public function getElapsedTime(): float {
        return $this->elapsed_time;
    }

}
