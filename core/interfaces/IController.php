<?php

namespace core\interfaces;

use core\abstracts\AResponse;

interface IController {

	public function indexAction(): AResponse;

}