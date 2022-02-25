<?php

namespace core\abstracts;

use core\interfaces\IController;
use core\System;
use core\classes\Logger;
use core\classes\Request;

/**
 * The Abstract version of a Controller
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AController implements IController {

	// A Logger for debug information
	protected Logger $_logger;
	// The object the represents the curren Request
	protected Request $_request;

	/**
	 * Sets instances from the System and makes them accessible to all
	 * Controllers
	 */
	public function init(): void {
		$this->_logger = System::getInstance()->getDebugLogger();
		$this->_request = System::getInstance()->getRequest();
	}

	public function __toString(): string {
		return __CLASS__;
	}
}