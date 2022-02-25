<?php

namespace core\abstracts;

use core\interfaces\IController;

/**
 * The Abstract version of a Controller
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AController implements IController {

	public function __toString(): string {
		return __CLASS__;
	}
}