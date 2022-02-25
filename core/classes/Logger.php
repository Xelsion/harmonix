<?php

namespace core\classes;

use DateTime;

/**
 * The Logger
 * creates a Logfile and stores log information
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Logger extends File {

	// The log text format
	private string $_log_line = "[%s]: [%s][%d]\n\t=> %s\n";

	/**
	 * The class constructor
	 *
	 * @param string $log_file
	 */
	public function __construct( string $log_type ) {
		$log_path = $this->getLogPath($log_type);
		parent::__construct($log_path);
	}

	/**
	 * Appends the formatted log text to the log file
	 * Return true if successful and false if not
	 *
	 * @param string $file
	 * @param int $line
	 * @param string $message
	 * @return bool
	 */
	public function log( string $file, int $line, string $message, array $backtrace = array() ): bool {
		$ts = new DateTime();
		$log = sprintf($this->_log_line, $ts->format("H:i:s"), $file, $line, $message);
		if( !empty($backtrace) ) {
			foreach( $backtrace as $trace ) {
				$log .= sprintf("\t=> Trace:[%d] %s%s%s(%s)\n", $trace["line"], $trace["class"], $trace["type"], $trace["function"], implode(", ", $trace["args"]));
			}
		}
		return $this->append($log);
	}

	/**
	 * creates the log folder structure and returns the
	 * path with a formatted file name
	 * folder structure: {log directory}/{year}/{month_name}/{day-weekday}_{file name from constructor}
	 *
	 * @param string $log_type
	 * @return string
	 */
	private function getLogPath( string $log_type ): string {
		$ts = new DateTime();
		$year = strtolower($ts->format("Y"));
		$month = strtolower($ts->format("F"));
		$log_file = $ts->format("d-D").".log";
		$log_path = PATH_LOGS.$log_type.DIRECTORY_SEPARATOR.$year.DIRECTORY_SEPARATOR.$month;
		if( !file_exists($log_path) && !mkdir($log_path, 0777, true) && !is_dir($log_path) ) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $log_path));
		}
		return $log_path.DIRECTORY_SEPARATOR.$log_file;
	}
}