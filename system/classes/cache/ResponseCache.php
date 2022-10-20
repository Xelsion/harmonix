<?php

namespace system\classes\cache;

use system\Core;
use system\classes\CacheFile;
use system\classes\File;
use system\exceptions\SystemException;

class ResponseCache {

    private static ?ResponseCache $_instance = null;

    private CacheFile $_cache;
    private array $_db_checks = array();
    private array $_file_checks = array();

    /**
     */
    private function __construct() {

    }

    /**
     * @return ResponseCache
     */
    public static function getInstance(): ResponseCache {
        if( static::$_instance === null ) {
            static::$_instance = new ResponseCache();
        }
        return static::$_instance;
    }

    public function initCacheFor( string $cache_name, ...$param ): void {
        $entries = array();
        foreach( $param as $p ) {
            if( is_object($p) ) {
                $entries[] = serialize($p);
            } else {
                $entries[] = (string)$p;
            }
        }
        $this->_cache = new CacheFile( $cache_name, implode("-", $entries) );
    }

    /**
     * @return string
     */
    public function getContent(): string {
        return $this->_cache->getContent();
    }

    /**
     * @param string $content
     *
     * @return void
     *
     * @throws SystemException
     */
    public function saveContent( string $content ): void {
        $this->_cache->saveToCache($content);
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
        $this->_file_checks[] = $file_name;
    }

    /**
     * Returns if the cache file age is up-to-date
     *
     * @return bool
     *
     */
    public function isUpToDate(): bool {
        return ($this->doDBCheck() && $this->doFileChecks());
    }

    /**
     * Reads the last modification date for the given table ind the given db
     *
     * @return bool
     *
     */
    private function doDBCheck(): bool {
        foreach( $this->_db_checks as $dbname => $tables ) {
            $pdo = Core::$_connection_manager->getConnection($dbname);
            foreach( $tables as $table_name ) {
                $table_time = $pdo->getModificationTimeOfTable($table_name);
                if( !$this->_cache->isUpToDate($table_time) ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    private function doFileChecks(): bool {
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
