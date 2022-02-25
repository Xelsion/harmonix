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
	private string $_log_line = "[%s]: [%s][%d]\n\t=> %s";

	/**
	 * The class constructor
	 *
	 * @param string $log_file
	 */
	public function __construct( string $log_file ) {
		$log_path = $this->getLogPath($log_file);
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
	public function log( string $file, int $line, string $message ): bool {
		$ts = new DateTime();
		$log = sprintf($this->_log_line, $ts->format("H:i:s"), $file, $line, $message);
		return $this->append($log);
	}

	/**
	 * creates the log folder structure and returns the
	 * path with a formatted file name
	 * folder structure: {log directory}/{year}/{month_name}/{day-weekday}_{file name from constructor}
	 *
	 * @param string $log_file
	 * @return string
	 */
	private function getLogPath( string $log_file ): string {
		$ts = new DateTime();
		$year = strtolower($ts->format("Y"));
		$month = strtolower($ts->format("F"));
		$log_suffix = $ts->format("d-D_");
		$log_path = PATH_LOGS.$year.DIRECTORY_SEPARATOR.$month;
		if( !file_exists($log_path) && !mkdir($log_path, 0777, true) && !is_dir($log_path) ) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $log_path));
		}
		return $log_path.DIRECTORY_SEPARATOR.$log_suffix.$log_file;
	}
}