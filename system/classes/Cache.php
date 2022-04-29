<?php

namespace system\classes;

use DateTime;
use system\exceptions\SystemException;
use system\helper\StringHelper;

/**
 * The Cache
 * Cache dato into a file
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Cache extends File {

    // the age of the cache file
    private DateTime $cache_age;

    /**
     * The constructor
     *
     * @param string $file_name
     */
    public function __construct( string $file_name ) {
        $cache_file = PATH_CACHE.md5($file_name).".cache";
        parent::__construct( $cache_file );
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
    public function isUpToDate( int $timestamp ) : bool {
        if( !file_exists($this->_file_path) ) {
            return false;
        }
        $data_age = new DateTime();
        $data_age->setTimestamp($timestamp);
        return $this->cache_age >= $data_age;
    }

    /**
     * Saves the given content into the cache file as an
     * encrypted string.
     *
     * @param string $content
     * @return void
     * @throws SystemException
     */
	public function saveToCache( string $content ) : void {
		$this->setContent(StringHelper::encrypt($content));
        $this->save();
	}

    /**
     * Loads the content of the cache file and returns its decrypted
     * content.
     *
     * @return string
     */
	public function loadFromCache() : string {
        return StringHelper::decrypt($this->getContent());
	}

}