<?php

namespace system\abstracts;

use system\classes\tree\TreeNode;
use system\interfaces\IEntity;
use PDO;

abstract class AEntityTreeNode extends TreeNode implements IEntity {

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