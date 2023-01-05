<?php

namespace system\abstracts;

use system\classes\Router;
use system\exceptions\SystemException;
use system\System;


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
abstract class AController {

    protected static ?bool $caching = null;

    public function __construct() {
        if( self::$caching === null ) {
            $environment = System::$Core->configuration->getSectionValue("system", "environment");
            self::$caching = (bool)System::$Core->configuration->getSectionValue($environment, "caching");
        }
    }

	public function __toString(): string {
		return __CLASS__;
	}
}
