<?php

namespace core\interfaces;

/**
 * The Response interface
 * Defines necessary methods for all responses
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
interface IResponse {

	/**
	 * sets the headers for the response
	 */
	public function setHeaders(): void;

}