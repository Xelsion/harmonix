<?php

namespace system\classes;

/**
 * The TimeAnalyser class
 * Can be used the set timers for different proposals
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class TimeAnalyser {

    /* The timers */
    private array $timers = array();

    /**
     * The class constructor
     */
    public function __construct() {

    }

    /**
     * Adds a time for the given key with the given label
     *
     * @param string $key
     * @param string $label
     *
     * @return void
     */
    public function addTimer(string $key, string $label = ""): void {
        $this->timers[$key] = array(
            "label" => $label,
            "timer" => new Timer()
        );
    }

    /**
     * Returns the timer for the given key or NULL
     *
     * @param string $key
     *
     * @return Timer|null
     */
    public function getTimer(string $key): ?Timer {
        return $this->timers[$key] ?? null;
    }

    /**
     * Returns an array of all timers
     *
     * @return array
     */
    public function getTimers(): array {
        return $this->timers;
    }

    /**
     * Starts the timer with the given key
     *
     * @param string $key
     *
     * @return bool
     */
    public function startTimer(string $key): bool {
        if( array_key_exists($key, $this->timers) ) {
            $this->timers[$key]["timer"]->start();
            return true;
        }
        return false;
    }

    /**
     * Stops  the timer with the given key
     *
     * @param string $key
     *
     * @return bool
     */
    public function stopTimer( string $key ): bool {
        if( array_key_exists($key, $this->timers) ) {
            $this->timers[$key]["timer"]->stop();
            return true;
        }
        return false;
    }

    /**
     * Returns the elapsed time of the timer with the given key
     *
     * @param string $key
     *
     * @return float
     */
    public function getTimerElapsedTime(string $key): float {
        if( array_key_exists($key, $this->timers) ) {
            return $this->timers[$key]["timer"]->getElapsedTime();
        }
        return 0.0;
    }

    /**
     * Returns the label of the timer with the given key
     *
     * @param string $key
     *
     * @return string
     */
    public function getTimerLabel(string $key): string {
        if( array_key_exists($key, $this->timers) ) {
            return $this->timers[$key]["label"];
        }
        return "";
    }

}
