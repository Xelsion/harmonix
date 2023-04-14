<?php
namespace lib\core\blueprints;

use lib\core\classes\Configuration;

/**
 * The Abstract version of a Controller.
 * A Controller register methods for a specific url that we call a route,
 * it can also add MenuItems to the Menu.
 * Alle methods registered as a route must return a Response
 *
 * @see \lib\core\Router
 * @see \lib\core\tree\Menu
 * @see \lib\core\tree\MenuItem
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AController {

    protected static ?bool $caching = null;

    /**
     * The class constructor
     *
     * @param Configuration $config
     */
    public function __construct(Configuration $config) {
        if( self::$caching === null ) {
            $environment = $config->getSectionValue("system", "environment");
            self::$caching = (bool)$config->getSectionValue($environment, "caching");
        }
    }

	public function __toString(): string {
		return __CLASS__;
	}
}
