<?php

namespace system\helper;

use PDO;

class SqlHelper {

    /**
     * Returns the PDO::PARAM type of the given value
     *
     * @param $value
     * @return int
     */
    public static function getParamType( $value ): int {
        if( is_null($value) ) {
            return PDO::PARAM_NULL;
        }

        if( is_bool($value) ) {
            return PDO::PARAM_BOOL;
        }

        if( preg_match("/^[0-9]+$/", $value) ) {
            return PDO::PARAM_INT;
        }

        return PDO::PARAM_STR;
    }

}
