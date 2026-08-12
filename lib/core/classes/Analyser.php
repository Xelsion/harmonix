<?php

namespace lib\core\classes;

use lib\core\enums\TimeFormat;

/**
 * The Analyser class.
 * Can be used to collect timers for different purposes.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Analyser extends StopWatch {

	/* The timers */
	private array $entries = array();

	/**
	 * Adds a time for the given key with the given label
	 *
	 * @param string $info
	 * @param bool $backtracking
	 * @return static
	 */
	public function add(string $info, bool $backtracking = false): static {
		if( $this->isRunning() ) {
			$this->stop();
		}

		$caller = [];
		if( $backtracking ) {
			$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
			$caller = $bt[1] ?? [];
			unset($caller["type"]);
		}

		$this->entries[] = [
			"time"           => $this->getLastMeasuredTime(),
			"time_formatted" => $this->getLastMeasuredTimeFormatted(TimeFormat::MILLI, 4),
			"info"           => $info,
			"backtrace"      => $caller
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
