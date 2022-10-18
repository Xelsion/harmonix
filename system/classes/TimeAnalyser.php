<?php

namespace system\classes;

/**
 * The TimeAnalyser class
 * Can be used the store db results in a cache file
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class TimeAnalyser {

    private array $_timers = array();

    public function __construct() {

    }

    public function addTimer(string $key, string $label = ""): void {
        $this->_timers[$key] = array(
            "label" => $label,
            "timer" => new Timer()
        );
    }

    public function getTimer(string $key): ?Timer {
        return $this->_timers[$key] ?? null;
    }

    public function getTimers(): array {
        return $this->_timers;
    }

    public function startTimer(string $key): bool {
        if( array_key_exists($key, $this->_timers) ) {
            $this->_timers[$key]["timer"]->start();
            return true;
        }
        return false;
    }

    public function stopTimer( string $key ): bool {
        if( array_key_exists($key, $this->_timers) ) {
            $this->_timers[$key]["timer"]->stop();
            return true;
        }
        return false;
    }

    public function getTimerElapsedTime(string $key): float {
        if( array_key_exists($key, $this->_timers) ) {
            return $this->_timers[$key]["timer"]->getElapsedTime();
        }
        return 0.0;
    }

    public function getTimerLabel(string $key): string {
        if( array_key_exists($key, $this->_timers) ) {
            return $this->_timers[$key]["label"];
        }
        return "";
    }

}
