<?php

namespace core\abstracts;

use \core\classes\tree\Node;
use \core\interfaces\IEntity;
use PDO;

abstract class AEntityNode extends Node implements IEntity {

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