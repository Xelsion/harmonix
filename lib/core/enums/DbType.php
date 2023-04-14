<?php

namespace lib\core\enums;

namespace lib\core\enums;

enum DbType: int {

    case MySQL = 1;
    case MsSQL = 2;
    case Postgres = 3;

    /**
     * Returns a string representing the
     *
     * @return string
     */
    public function toString(): string {
        return match($this) {
            self::MySQL => "MySql",
            self::MsSQL => "MsSql",
            self::Postgres => "Postgres"
        };
    }

}