<?php

namespace lib\core\classes;

use lib\core\enums\TimeFormat;

class StopWatch {

	private bool $is_running = false;

	protected int $activation_time = 0;

	protected int $start_time = 0;

	protected int $stop_time = 0;

	private array $measured_times = array();

	private int $measured_time_total = 0;

	private const string KEY_START = "start";
	private const string KEY_STOP = "stop";
	private const string KEY_ELAPSED = "elapsed";

	public function __construct() {

	}

	/**
	 * Starts the stopwatch
	 *
	 * @return $this
	 */
	public function start(): static {
		$now = $this->getTime();
		if( $this->activation_time === 0 ) {
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
	public function stop(): static {
		if( $this->isRunning() ) {
			$this->stop_time = $this->getTime();
			$stopped_time = $this->stop_time - $this->start_time;
			$this->measured_times[] = [
				self::KEY_START   => $this->start_time,
				self::KEY_STOP    => $this->stop_time,
				self::KEY_ELAPSED => $stopped_time
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
	public function reset(): static {
		$this->activation_time = 0;
		$this->start_time = 0;
		$this->stop_time = 0;
		$this->measured_times = [];
		$this->measured_time_total = 0;
		$this->is_running = false;
		return $this;
	}

	/**
	 * Returns if the stop watch is running
	 * @return bool
	 */
	public function isRunning(): bool {
		return $this->is_running;
	}

	/**
	 * Returns all measured time in an array
	 *
	 * @return array
	 */
	public function getMeasuredTimes(): array {
		return $this->measured_times;
	}

	/**
	 * Returns all measured time in an array with formatted time values
	 *
	 * @param TimeFormat $format
	 * @param int $precision
	 * @return array
	 */
	public function getMeasuredTimesFormatted(TimeFormat $format = TimeFormat::SEC, int $precision = 2): array {
		return array_map(fn($times): array => [
			self::KEY_START   => $this->format($times[self::KEY_START], $format, $precision),
			self::KEY_STOP    => $this->format($times[self::KEY_STOP], $format, $precision),
			self::KEY_ELAPSED => $this->format($times[self::KEY_ELAPSED], $format, $precision),
		], $this->measured_times);
	}

	/**
	 * Sets the return value to the elapsed time between the last start and stop
	 *
	 * @return int
	 */
	public function getLastMeasuredTime(): int {
		if( !empty($this->measured_times) ) {
			$last_key = array_key_last($this->measured_times);
			return $this->measured_times[$last_key][self::KEY_ELAPSED];
		}
		return 0;
	}

	/**
	 * Sets the return value to the elapsed time between the last start and stop with formatted time values
	 *
	 * @param TimeFormat $format
	 * @param int $precision
	 * @return string
	 */
	public function getLastMeasuredTimeFormatted(TimeFormat $format = TimeFormat::SEC, int $precision = 2): string {
		if( !empty($this->measured_times) ) {
			$last_key = array_key_last($this->measured_times);
			return static::format($this->measured_times[$last_key][self::KEY_ELAPSED], $format, $precision);
		}
		return static::format(0, $format, $precision);
	}

	/**
	 * Sets the return value to a sum of the times between all starts and stops
	 *
	 * @return int
	 */
	public function getTotalMeasuredTime(): int {
		return $this->measured_time_total;
	}

	/**
	 * Sets the return value to a sum of the times between all starts and stops with formatted time values
	 *
	 * @param TimeFormat $format
	 * @param int $precision
	 * @return string
	 */
	public function getTotalMeasuredTimeFormatted(TimeFormat $format = TimeFormat::SEC, int $precision = 2): string {
		return static::format($this->measured_time_total, $format, $precision);
	}

	/**
	 * Sets the return value the actual time and the time the stopwatch was started the first time
	 *
	 * @return int
	 */
	public function getElapsedTime(): int {
		if( $this->activation_time > 0 ) {
			return $this->getTime() - $this->activation_time;
		}
		return 0;
	}

	/**
	 * Sets the return value the actual time and the time the stopwatch was started the first time with formatted time values
	 *
	 * @param TimeFormat $format
	 * @param int $precision
	 * @return string
	 */
	public function getElapsedTimeFormatted(TimeFormat $format = TimeFormat::SEC, int $precision = 2): string {
		if( $this->activation_time > 0 ) {
			return static::format(($this->getTime() - $this->activation_time), $format, $precision);
		}
		return static::format(0, $format, $precision);
	}

	/**
	 * Returns the return value in the given time format:
	 * valid time formats art: µs, ms, s, m and h default is ns
	 *
	 * @param int $time
	 * @param TimeFormat $format
	 * @param int $precision
	 * @return string
	 */
	public static function format(int $time, TimeFormat $format = TimeFormat::SEC, int $precision = 2): string {
		$number = (float)$time / $format->getDivider();
		return number_format($number, $precision, ",", ".") . " " . $format->toString();
	}

	/**
	 * Returns the actual time in nanoseconds
	 *
	 * @return int
	 */
	protected function getTime(): int {
		return hrtime(true);
	}

}