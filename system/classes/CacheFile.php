<?php

namespace system\classes;

use DateTime;
use system\Core;
use system\exceptions\SystemException;
use system\helper\StringHelper;

/**
 * The CacheFile
 * CacheFile dato into a file
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class CacheFile extends File {

    // the age of the cache file
    private DateTime $cache_age;
    private bool $_encrypt;

    /**
     * The constructor
     *
     * @param string $file_name
     */
    public function __construct( string $file_name, string $hash ) {
        $cache_setting = Core::$_configuration->getSection("cache");
        $this->_encrypt = $cache_setting["encryption"];

        $file_name = str_replace("::", "/", $file_name);

        $cache_file = PATH_CACHE . Core::$_actor->id . DIRECTORY_SEPARATOR . $file_name."_".(int)$this->_encrypt."_".md5($hash) . ".cache";
        parent::__construct($cache_file);
        $this->cache_age = new DateTime();
        if( file_exists($this->_file_path) ) {
            $this->cache_age->setTimestamp(filemtime($cache_file));
        } else {
            $this->cache_age->setTimestamp(0);
        }
    }

    /**
     * Loads the cache file with the given name
     *
     * @param string $cache_file
     * @return void
     */
    public function load( string $cache_file ): void {
        parent::__construct($cache_file);
        $this->cache_age = new DateTime();
        if( file_exists($this->_file_path) ) {
            $this->cache_age->setTimestamp(filemtime($cache_file));
        } else {
            $this->cache_age->setTimestamp(0);
        }
    }

    /**
     * Checks if the current cache file is not older than
     * the given timestamp.
     *
     * @param int $timestamp
     * @return bool
     */
    public function isUpToDate( int $timestamp ): bool {
        if( !file_exists($this->_file_path) ) {
            return false;
        }
        $data_age = new DateTime();
        $data_age->setTimestamp($timestamp);
        if( $this->cache_age < $data_age ) {
            $this->delete();
            return false;
        }
        return true;
    }

    /**
     * Saves the given content into the cache file as an
     * encrypted string.
     *
     * @param string $content
     *
     * @return void
     *
     * @throws SystemException
     */
    public function saveToCache( string $content ): void {
        if( $this->_encrypt ) {
            $this->setContent(StringHelper::encrypt($content));
        } else {
            $this->setContent($content);
        }
        $this->save();
    }

    /**
     * Loads the content of the cache file and returns its decrypted
     * content.
     *
     * @return string
     */
    public function loadFromCache(): string {
        if( $this->_encrypt ) {
            return StringHelper::decrypt($this->getContent());
        }
        return $this->getContent();
    }

}
