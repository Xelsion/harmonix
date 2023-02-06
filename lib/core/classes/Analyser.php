<?php
namespace lib\core\classes;

/**
 * The Analyser class
 * Can be used the setClass timers for different proposals
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Analyser {

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
    public function addTimer(Timer $timer): void {
        $this->timers[] = $timer;
    }

    /**
     * Returns the timer for the given label or NULL
     *
     * @param string $label
     *
     * @return Timer|null
     */
    public function getTimerByLabel(string $label): ?Timer {
        foreach( $this->timers as $timer ) {
            if( $timer->getLabel() === $label ) {
                return $timer;
            }
        }
        return null;
    }

    /**
     * Returns the timer for the given key or NULL
     *
     * @param string $key
     *
     * @return Timer|null
     */
    public function getTimers(): array {
        return $this->timers;
    }

}
