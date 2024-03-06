<?php

namespace lib\core\classes;

class StopWatch {

	protected bool $is_running = false;

	public float $activation_time = 0.0;

	public float $start_time = 0.0;

	public float $stop_time = 0.0;

	private array $measured_times = array();

	private float $measured_time_total = 0.0;

	private float $return_value = 0.0;

	public function __construct() {

	}

	/**
	 * Starts the stopwatch
	 *
	 * @return $this
	 */
	public function start(): StopWatch {
		$now = $this->getTime();
		if( $this->start_time === 0.0 ) {
			$this->activation_time = $now;
		}
		$this->start_time = $now;
		$this->is_running = true;
		return $this;
	}

	/**
	 * Stops the stopwatch
	 *
	 * @return $this
	 */
	public function stop(): StopWatch {
		if( $this->is_running ) {
			$this->stop_time = $this->getTime();
			$stopped_time = $this->stop_time - $this->start_time;
			$this->measured_times[] = [
				"start"   => $this->stop_time,
				"stop"    => $this->stop_time,
				"elapsed" => $stopped_time
			];
			$this->measured_time_total += $stopped_time;
			$this->is_running = false;
		}
		return $this;
	}

	/**
	 * Resets the stopwatch
	 *
	 * @return $this
	 */
	public function reset(): StopWatch {
		$this->start_time = 0.0;
		$this->stop_time = 0.0;
		$this->measured_times = [];
		$this->measured_time_total = 0.0;
		$this->is_running = false;
		return $this;
	}

	/**
	 * Sets the return value to the elapsed time between the last start and stop
	 *
	 * @return $this
	 */
	public function getLastMeasuredTime(): StopWatch {
		$this->return_value = 0.0;
		if( $this->is_running ) {
			$this->stop();
		}
		if( !empty($this->measured_times) ) {
			$last_measured_time = $this->measured_times[count($this->measured_times) - 1];
			$this->return_value = $last_measured_time["elapsed"];
		}
		return $this;
	}

	/**
	 * Returns all measured time in an array
	 * @return array
	 */
	public function getMeasuredTimes(): array {
		if( $this->is_running ) {
			$this->stop();
		}
		return $this->measured_times;
	}

	/**
	 * Sets the return value to a sum of the times between all starts and stops
	 *
	 * @return $this
	 */
	public function getTotalMeasuredTime(): StopWatch {
		if( $this->is_running ) {
			$this->stop();
		}
		$this->return_value = $this->measured_time_total;
		return $this;
	}

	/**
	 * Sets the return value the actual time and the time the stopwatch was started the first time
	 *
	 * @return $this
	 */
	public function getElapsedTime(): StopWatch {
		$this->return_value = 0.0;
		if( $this->activation_time > 0 ) {
			$this->return_value = ($this->getTime() - $this->activation_time);
		}
		return $this;
	}

	/**
	 * Returns the return value in the given time format:
	 * valid time formats art: µs, ms, s, m and h
	 *
	 * @param string $format
	 * @param int $precision
	 * @return string
	 */
	public function format(string $format = "s", int $precision = 2): string {
		$secToMilliSec = 1000;
		$secToMicroSec = $secToMilliSec * 1000;
		$secToMinutes = 60;
		$secToHours = $secToMinutes * 60;
		$number = match ($format) {
			"µs" => round($this->return_value * $secToMicroSec, $precision),
			"ms" => round($this->return_value * $secToMilliSec, $precision),
			"m" => round($this->return_value / $secToMinutes, $precision),
			"h" => round($this->return_value / $secToHours, $precision),
			default => round($this->return_value, $precision)
		};
		return number_format($number, $precision, ",", ".") . $format;
	}

	/**
	 * Returns the actual time in seconds
	 *
	 * @return float
	 */
	protected function getTime(): float {
		return microtime(true);
	}

}