<?php

namespace system\abstracts;

use system\Core;
use system\interfaces\IController;

/**
 * The Abstract version of a Controller.
 * A Controller register methods for a specific url that we call a route,
 * it can also add MenuItems to the Menu.
 * Alle methods registered as a route must return a Response
 *
 * @see \system\interfaces\IController
 * @see \system\classes\Router
 * @see \system\classes\tree\Menu
 * @see \system\classes\tree\MenuItem
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AController extends Core implements IController {

	public function __toString(): string {
		return __CLASS__;
	}
}