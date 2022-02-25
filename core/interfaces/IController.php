<?php

namespace core\interfaces;

use core\abstracts\AResponse;
use core\classes\Router;

/**
 * The controller interface
 * Defines necessary methods for all controllers
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
interface IController {

	/**
	 * This function will add all routes it has
	 * to the given Router
	 *
	 * @param Router $router
	 */
	public function initRoutes( Router $router ): void;

	/**
	 * The default method that will be called if no
	 * specific method was requested by the request.
	 *
	 * @return AResponse
	 */
	public function indexAction(): AResponse;

}