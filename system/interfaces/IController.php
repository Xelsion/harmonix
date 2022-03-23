<?php

namespace system\interfaces;

use system\abstracts\AResponse;
use system\classes\Router;

/**
 * The controller interface
 * Defines necessary methods for all controllers
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
interface IController {

	/**
	 * This function will add all routes it has to the given Router
	 * It can also add MenuItems to the navigation Menu (The Menu
	 * is accessible through the Core class)
	 *
	 * @param Router $router
	 * @see \system\Core
	 */
	public function init( Router $router ): void;

    /**
     * Returns all routes of the controller in an array.
     * The array structure is like:
     * <p>
     * [
     *      url => array[
     *          "controller" => the controller class,
     *          "method" => the controller method
     *      ],
     *      ...
     * ]
     * </p>
     *
     * @return array
     */
    public function getRoutes() : array;

	/**
	 * The default method that will be called if no
	 * specific method was requested by the request.
	 *
	 * @return AResponse
	 */
	public function index(): AResponse;

}