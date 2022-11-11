<?php

namespace system\classes\cache;

use system\System;
use system\classes\CacheFile;
use system\classes\File;
use system\exceptions\SystemException;


/**
 * The ResponseCache class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ResponseCache {

    private static ?ResponseCache $_instance = null;

    private CacheFile $cache;
    private array $db_checks = array();
    private array $file_checks = array();

    /**
     * The class constructor
     * will be called once by the static method getInstance()
     *
     */
    private function __construct() {

    }

    /**
     * The initializer for this class
     *
     * @return ResponseCache
     */
    public static function getInstance(): ResponseCache {
        if( static::$_instance === null ) {
            static::$_instance = new ResponseCache();
        }
        return static::$_instance;
    }

    /**
     * Initializes a cache file with a combination of the given name and the
     * parameters
     *
     * @param string $cache_name
     * @param ...$param
     *
     * @return void
     */
    public function initCacheFor( string $cache_name, ...$param ): void {
        $entries = array();
        foreach( $param as $p ) {
            if( is_object($p) ) {
                $entries[] = serialize($p);
            } else {
                $entries[] = (string)$p;
            }
        }
        $this->cache = new CacheFile( $cache_name, implode("-", $entries) );
    }

    /**
     * Returns the content of the current cache file
     *
     * @return string
     */
    public function getContent(): string {
        return $this->cache->getContent();
    }

    /**
     * Saves the given content to the current cache file
     *
     * @param string $content
     *
     * @return void
     *
     * @throws SystemException
     */
    public function saveContent( string $content ): void {
        $this->cache->saveToCache($content);
    }

    /**
     * Adds the given table of the given database to the checklist
     *
     * @param $db_name
     * @param $table_name
     *
     * @return void
     */
    public function addDBCheck( $db_name, $table_name ): void {
        if( array_key_exists($db_name, $this->db_checks) && in_array($table_name, $this->db_checks[$db_name], TRUE) ) {
            return;
        }
        $this->db_checks[$db_name][] = $table_name;
    }

    /**
     * Adds the given file to the checklist
     *
     * @param $file_name
     *
     * @return void
     */
    public function addFileCheck( $file_name ): void {
        if( in_array($file_name, $this->file_checks, TRUE) ) {
            return;
        }
        $this->file_checks[] = $file_name;
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
     * Reads the last modification date of all tables in the current checklist
     *
     * @return bool
     *
     */
    private function doDBCheck(): bool {
        foreach( $this->db_checks as $dbname => $tables ) {
            $pdo = System::$Core->connection_manager->getConnection($dbname);
            foreach( $tables as $table_name ) {
                $table_time = $pdo->getModificationTimeOfTable($table_name);
                if( !$this->cache->isUpToDate($table_time) ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Reads the last modification date of all files in the current checklist
     *
     * @return bool
     */
    private function doFileChecks(): bool {
        $cache_time = $this->cache->getLastModified();
        foreach( $this->file_checks as $file_name ) {
            $file = new File($file_name);
            $file_time = $file->getLastModified();
            if( $file_time > $cache_time ) {
                return false;
            }
        }
        return true;
    }

}
