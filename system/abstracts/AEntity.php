<?php

namespace system\abstracts;

use DateTime;
use PDO;

use system\interfaces\IEntity;

/**
 * The Abstract version of a Entity
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

	/**
	 * Returns the PDO::PARAM type of the given value
	 *
	 * @param $value
	 * @return int
	 */
	protected static function getParamType( $value ): int {
		if( is_null($value) ) {
			return PDO::PARAM_NULL;
		}
		if( preg_match("/^[0-9]+$/", $value) ) {
			return PDO::PARAM_INT;
		}
		return PDO::PARAM_STR;
	}

}