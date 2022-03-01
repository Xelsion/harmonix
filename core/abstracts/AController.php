<?php

namespace core\abstracts;

use core\interfaces\IController;
use core\System;
use core\classes\Configuration;
use core\classes\Logger;
use core\classes\Request;
use core\manager\ConnectionManager;
use models\Actor;

/**
 * The Abstract version of a Controller
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AController implements IController {

	// The application configuration
	protected Configuration $_config;
	// A Logger for debug information
	protected Logger $_logger;
	// The connection manager for database usage
	protected ConnectionManager $_connection_manager;
	// The object the represents the curren Request
	protected Request $_request;
	// The current logged in actor
	protected Actor $_actor;

	/**
	 * Sets instances from the System and makes them accessible to all
	 * Controllers
	 */
	public function init(): void {
		$this->_config = Configuration::getInstance();
		$this->_logger = System::getInstance()->getDebugLogger();
		$this->_request = System::getInstance()->getRequest();
		$this->_connection_manager = System::getInstance()->getConnectionManager();
		$this->_actor = System::getInstance()->getActor();
	}

	public function __toString(): string {
		return __CLASS__;
	}
}