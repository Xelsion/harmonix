<?php
namespace lib\core\classes;

/**
 * The Time class
 * Can be used to measure time
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Timer {

    private string $label;
    private float $start;
    private float $elapsed_time = 0.0;
    private array $backtrace;

    /**
     * The class constructor
     */
    public function __construct(string $label) {
        $this->label = $label;
        $this->backtrace = debug_backtrace();
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
     * Returns the label of this timer
     *
     * @return string
     */
    public function getLabel(): string {
        return $this->label;
    }

    /**
     * Returns the backtrace of this timer
     *
     * @return array
     */
    public function getTrace(): array {
        return $this->backtrace;
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
