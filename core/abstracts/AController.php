<?php

namespace core\abstracts;

use core\Core;
use core\interfaces\IController;

/**
 * The Abstract version of a Controller.
 * A Controller register methods for a specific url that we call a route,
 * it can also add MenuItems to the Menu.
 * Alle methods registered as a route must return a Response
 *
 * @see \core\interfaces\IController
 * @see \core\classes\Router
 * @see \core\classes\tree\Menu
 * @see \core\classes\tree\MenuItem
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AController implements IController {

	public function __toString(): string {
		return __CLASS__;
	}
}