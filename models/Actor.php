<?php

namespace models;

use core\System;
use PDO;

class Actor extends entities\Actor {

    public function find( array $conditions ) {
        if( empty( $conditions ) ) {
            return $this->findAll();
        }

        $columns = array();
        foreach( array_keys( $conditions ) as $col ) {
            $columns[] = $col.":".$col;
        }

        $pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
        $sql = "SELECT * FROM actors WHERE ".implode(" AND ", $columns);
        $stmt = $pdo->prepare($sql);
        foreach( $conditions as $key => $val ) {
            $stmt = $pdo->bindParam(":".$key, $val, $this->getParamType($val));
        }
        $stmt->setFetchMode(PDO::FETCH_OBJ, __CLASS__);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findAll() {
        $pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
        $stmt = $pdo->prepare("SELECT * FROM actors");
        $stmt->setFetchMode(PDO::FETCH_OBJ, __CLASS__);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getParamType( $value ) : ?int {
        if( is_null($value) ) {
            return PDO::PARAM_NULL;
        }

        if( preg_match("/^[0-9]+$", $value) ) {
            return PDO::PARAM_INT;
        }
        return PDO::PARAM_STR;
    }

    public function toTableRow() {
        return "<div></div>";
    }

}