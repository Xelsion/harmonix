<?php

namespace core\abstracts;

use DateTime;
use PDO;

use core\interfaces\IEntity;

abstract class AEntity implements IEntity {

	public function str2DateTime( string $datetime ) {
		return DateTime::createFromFormat("Y-m-d H:i:s", $datetime);
	}

	protected static function getParamType( $value ): ?int {
		if( is_null($value) ) {
			return PDO::PARAM_NULL;
		}
		if( preg_match("/^[0-9]+$/", $value) ) {
			return PDO::PARAM_INT;
		}
		return PDO::PARAM_STR;
	}

}