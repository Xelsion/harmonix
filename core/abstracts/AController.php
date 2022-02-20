<?php

namespace core\abstracts;

use core\interfaces\IController;

abstract class AController implements IController {

	public function __toString(): string {
		return __CLASS__;
	}
}