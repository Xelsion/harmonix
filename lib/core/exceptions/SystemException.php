<?php

namespace lib\core\exceptions;

use lib\core\blueprints\ALoggableException;

/**
 * The System Exception will be used to catch every exception
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class SystemException extends ALoggableException {

	/**
	 * @inheritDoc
	 */
	public function log(): void {
		$this->logger->log($this->file, $this->line, $this->message, $this->getTrace());
	}

}
