<?php

namespace system\abstracts;

use DateTime;
use PDO;

use system\interfaces\IEntity;

/**
 * The Abstract version of an Entity
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class AEntity implements IEntity {

	/**
	 * Converts a string to a DateTime object
	 *
	 * @param string $datetime
	 * @return DateTime|false
	 */
	public function str2DateTime( string $datetime ) {
		return DateTime::createFromFormat("Y-m-d H:i:s", $datetime);
	}
}