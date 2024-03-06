<?php

namespace lib\core\classes;

/**
 * The Analyser class
 * Can be used the setClass timers for different proposals
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Analyser extends StopWatch {

	/* The timers */
	private array $entries = [];

	/**
	 * Adds a time for the given key with the given label
	 *
	 * @param string $info
	 * @return Analyser
	 */
	public function add(string $info): Analyser {
		if( $this->is_running ) {
			$this->stop();
		}
		$this->entries[] = [
			"time"      => $this->getLastMeasuredTime()->format("ms", 4),
			"info"      => $info,
			"backtrace" => debug_backtrace()
		];
		return $this;
	}

	/**
	 * Returns the entries
	 *
	 * @return array
	 */
	public function getEntries(): array {
		return $this->entries;
	}

}
