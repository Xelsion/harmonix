<?php

namespace system\classes;

use JsonException;
use RuntimeException;
use DateTime;
use system\exceptions\SystemException;

/**
 * The Logger
 * creates a Logfile and stores log information
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Logger extends File {

	// The log text format
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
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function log( string $file, int $line, string $message, array $backtrace = array() ): bool {
		$ts = new DateTime();
		// Crate the log string
        $log = sprintf("[%s] %s\n", $ts->format("H:i:s"), $message);
        $log .= sprintf("\t=>\tFile: %s [Line %d]\n", $file, $line);
		if( !empty($backtrace) ) {
			foreach( $backtrace as $trace ) {
				$args = array();
				// Go through all arguments and get a representable string for it
				foreach( $trace["args"] as $arg ) {
					if( is_object($arg) ) {
						$args[] = get_class($arg);
					} else {
						$args[] = $arg;
					}
				}
                $string_args = "";
                if( !empty($args) ) {
                    $string_args = json_encode($args, JSON_THROW_ON_ERROR);
                    $string_args = str_replace("\\\\", "\\", $string_args);
                }
				// Add the trace to the log string
				$log .= sprintf("\t=>\tTrace: %s%s%s(%s) [Line %d]\n", $trace["class"], $trace["type"], $trace["function"],$string_args, $trace["line"]);
			}
		}

		// Sets the actual file path
		$this->_file_path = $this->getLogPath($this->_log_type);
		$path_parts = pathinfo($this->_file_path);
		// Create all necessary folders
		if( !file_exists($path_parts["dirname"]) && !mkdir($path_parts["dirname"], 0777, true) && !is_dir($path_parts["dirname"]) ) {
			throw new SystemException(__FILE__, __LINE__, sprintf('Directory "%s" was not created', $path_parts["dirname"]));
		}
		return $this->append($log);
	}

	/**
	 * Returns the path with a formatted file name
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