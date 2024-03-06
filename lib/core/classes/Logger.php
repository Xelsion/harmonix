<?php

namespace lib\core\classes;

use DateTime;
use Exception;
use lib\core\exceptions\SystemException;

/**
 * The Logger
 * creates a Logfile and stores log information
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Logger extends File {

	// The log text format
	private string $log_type;

	/**
	 * The class constructor
	 *
	 * @param string $log_type
	 */
	public function __construct(string $log_type) {
		parent::__construct($this->getLogPath($log_type));
		$this->log_type = $log_type;
	}

	/**
	 * Appends the formatted log text to the log file
	 * Return true if successful and false if not
	 *
	 * @param string $file
	 * @param int $line
	 * @param string $message
	 * @param array $backtrace
	 * @return bool
	 *
	 * @throws \lib\core\exceptions\SystemException
	 */
	public function log(string $file_name, int $line_nr, string $message, array $backtrace = array()): bool {
		try {
			$ts = new DateTime();
			// Crate the log string
			$log = sprintf("[%s] %s\n", $ts->format("H:i:s"), $message);
			$log .= sprintf("\t=>\tFile: %s [Line %d]\n", $file_name, $line_nr);
			if( !empty($backtrace) ) {
				foreach( $backtrace as $trace ) {
					$args = array();
					// Go through all arguments and getInstanceOf a representable string for it
					if( isset($trace["args"]) ) {
						foreach( $trace["args"] as $arg ) {
							if( is_object($arg) ) {
								$args[] = get_class($arg);
							} else {
								$args[] = $arg;
							}
						}
					}
					$string_args = "";
					if( !empty($args) ) {
						$string_args = json_encode($args, JSON_THROW_ON_ERROR);
						$string_args = str_replace("\\\\", "\\", $string_args);
					}
					// Add the trace to the log string
					$class = $trace['class'] ?? '';
					$type = $trace['type'] ?? '';
					$function = $trace['function'] ?? '';
					$line = (isset($trace['line'])) ? (int)$trace['line'] : 0;
					$log .= sprintf("\t=>\tTrace: %s%s%s(%s) [Line %d]\n", $class, $type, $function, $string_args, $line);
				}
			}

			// Sets the actual file path
			$this->file_path = $this->getLogPath($this->log_type);
			return $this->append($log);
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, "Logger: Can't encode JSON data", $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Returns the path with a formatted file name
	 * folder structure: logs/{log directory}/{year}/{month_name}/{day-weekday}.log
	 * e.g. logs/debug/2022/august/12-Mon.log
	 *
	 * @param string $log_type
	 * @return string
	 */
	private function getLogPath(string $log_type): string {
		$ts = new DateTime();
		$year = strtolower($ts->format("Y"));
		$month = strtolower($ts->format("F"));
		$log_file = $ts->format("d-D") . ".log";
		$log_path = PATH_LOGS . $log_type . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month;
		return $log_path . DIRECTORY_SEPARATOR . $log_file;
	}
}
