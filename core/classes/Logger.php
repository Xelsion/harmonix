<?php

namespace core\classes;

use RuntimeException;
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
	private string $_log_type;

	/**
	 * The class constructor
	 *
	 * @param string $log_type
	 */
	public function __construct( string $log_type ) {
		$this->_log_type = $log_type;
		parent::__construct($this->getLogPath($log_type));
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
	 */
	public function log( string $file, int $line, string $message, array $backtrace = array() ): bool {
		$ts = new DateTime();
		$log = sprintf($this->_log_line, $ts->format("H:i:s"), $file, $line, $message);
		if( !empty($backtrace) ) {
			foreach( $backtrace as $trace ) {
				$log .= sprintf("\t=> Trace:[%d] %s%s%s(%s)\n", $trace["line"], $trace["class"], $trace["type"], $trace["function"], implode(", ", $trace["args"]));
			}
		}

		// sets the actual file path
		$this->_file_path = $this->getLogPath($this->_log_type);
		if( !file_exists($this->_file_path) && !mkdir($this->_file_path, 0777, true) && !is_dir($this->_file_path) ) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $this->_file_path));
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
		return $log_path.DIRECTORY_SEPARATOR.$log_file;
	}
}