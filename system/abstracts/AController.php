<?php

namespace system\abstracts;

use system\Core;
use system\classes\Router;
use system\exceptions\SystemException;

/**
 * The Abstract version of a Controller.
 * A Controller register methods for a specific url that we call a route,
 * it can also add MenuItems to the Menu.
 * Alle methods registered as a route must return a Response
 *
 * @see \system\classes\Router
 * @see \system\classes\tree\Menu
 * @see \system\classes\tree\MenuItem
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AController extends Core {

    protected static ?bool $caching = null;

    public function __construct() {
        if( self::$caching === null ) {
            $environment = Core::$_configuration->getSectionValue("system", "environment");
            self::$caching = (bool)Core::$_configuration->getSectionValue($environment, "caching");
        }
    }

    /**
     * This function will add all routes it has to the given Router
     * It can also add MenuItems to the navigation Menu (The Menu
     * is accessible through the Core class)
     *
     * @param Router $router
     * @see \system\Core
     *
     * @throws SystemException
     */
    abstract public function init( Router $router ): void;

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
    abstract public function getRoutes() : array;

    /**
     * The default method that will be called if no
     * specific method was requested by the request.
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    abstract public function index(): AResponse;

	public function __toString(): string {
		return __CLASS__;
	}
}
