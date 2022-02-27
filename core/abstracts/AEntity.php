<?php

namespace core\abstracts;

use core\interfaces\IEntity;
use DateTime;

abstract class AEntity implements IEntity {

	public function str2DateTime( string $datetime ) {
		return DateTime::createFromFormat("Y-m-d H:i:s", $datetime);
	}

}