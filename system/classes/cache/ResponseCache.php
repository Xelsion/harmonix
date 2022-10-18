<?php

namespace system\classes\cache;

use DateTime;
use Exception;
use JsonException;
use system\Core;
use system\exceptions\SystemException;

class ResponseCache {

    private static ResponseCache $_instance;

    private CacheFile $_cache;
    private array $_db_checks;
    private array $_file_checks;

    private function __construct( string $cache_name, ...$param ) {
        $this->_cache = new CacheFile( $cache_name . implode("-", $param) );
    }

    public static function getInstance( string $cache_name, ...$param ): ResponseCache {
        if( static::$_instance === null ) {
            static::$_instance = new ResponseCache( $cache_name, ...$param );
        }
        return static::$_instance;
    }

    /**
     * @param $db_name
     * @param $table_name
     *
     * @return void
     */
    public function addDBCheck( $db_name, $table_name ): void {
        if( array_key_exists($db_name, $this->_db_checks) && in_array($table_name, $this->_db_checks[$db_name], TRUE) ) {
            return;
        }
        $this->_db_checks[$db_name][] = $table_name;
    }

    /**
     * @param $file_name
     *
     * @return void
     */
    public function addFileCheck( $file_name ): void {
        if( in_array($file_name, $this->_file_checks, TRUE) ) {
            return;
        }
        $this->_db_checks[] = $file_name;
    }

    /**
     * Reads the last modification date for the given table ind the given db
     *
     * @return bool
     *
     * @throws SystemException
     * @throws JsonException
     * @throws Exception
     */
    public function doDBCheck(): bool {
        foreach( $this->_db_checks as $db => $tables ) {
            $pdo = Core::$_connection_manager->getConnection($db, false);
            $pdo->prepare("SELECT create_time, update_time FROM information_schema.tables WHERE table_schema=:db AND table_name IN (:table)");
            $pdo->bindParam("db", $db);
            $pdo->bindParam("table", implode("','", $tables));
            $results = $pdo->execute()->fetch();
            foreach( $results as $row ) {
                $create_time = !is_null($row["create_time"]);
                $update_time = ( !is_null($row["update_time"]) )
                    ? $row["update_time"]
                    : "1970-01-01 00:00:00";
                $create_date = new DateTime($create_time);
                $update_date = new DateTime($update_time);

                $modification_time = ( $update_date > $create_date )
                    ? $update_date->getTimestamp()
                    : $create_date->getTimestamp();

                if( !$this->_cache->isUpToDate($modification_time) ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function doFileChecks(): bool {
        $cache_time = $this->_cache->getLastModified();
        foreach( $this->_file_checks as $file_name ) {
            $file = new File($file_name);
            $file_time = $file->getLastModified();
            if( $file_time > $cache_time ) {
                return false;
            }
        }
        return true;
    }

}
