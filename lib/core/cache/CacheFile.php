<?php
namespace lib\core\cache;

use DateTime;
use lib\App;
use lib\core\classes\Configuration;
use lib\core\classes\File;
use lib\helper\StringHelper;

/**
 * The CacheFile
 * Stores any given content to file so u can load it on the same request
 * without to process the necessary functions that produces the content.
 * Mostly it is much faster to reuse the produced content from a file than
 * to rebuild it every time.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class CacheFile extends File {

    // the age of the cache file
    private DateTime $cache_age;
    private bool $encrypt;

    /**
     * The constructor
     *
     * if the methode (__METHOD__) name was given as file_name the result will be like:
     * {namespace}/{classname}/{method}/{actor_id}/{encrypted}_{hash}.cache
     * else it would be like
     * {file_path}/{filename}/{actor_id}/{encrypted}_{hash}.cache
     *
     * @param string $file_name
     * @param string $hash
     */
    public function __construct( Configuration $config, string $file_name = "", string $hash = "" ) {
        $cache_setting = $config->getSection("cache");
        $this->encrypt = $cache_setting["encryption"];

        if( $file_name !== "" ) {
            $file_name = str_replace("::", "/", $file_name);
            $path_infos = pathinfo($file_name);

            // build the cache file name
            $dir_part = PATH_CACHE_ROOT. $path_infos["dirname"] . DIRECTORY_SEPARATOR
                . $path_infos["filename"] . DIRECTORY_SEPARATOR
                . App::$curr_actor->id . DIRECTORY_SEPARATOR;
            $cache_file = $dir_part . (int)$this->encrypt."_".md5($hash) . ".cache";

            parent::__construct($cache_file);
            $this->cache_age = new DateTime();
            if( file_exists($this->file_path) ) {
                $this->cache_age->setTimestamp(filemtime($cache_file));
            } else {
                $this->cache_age->setTimestamp(0);
            }
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
        if( file_exists($this->file_path) ) {
            $creation_time = filectime($cache_file);
            $modification_time = filemtime($cache_file);
            $time = ( $modification_time > $creation_time ) ? $modification_time : $creation_time;
            $this->cache_age->setTimestamp($time);
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
        if( !file_exists($this->file_path) ) {
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
     * @throws \lib\core\exceptions\SystemException
     */
    public function saveToCache( string $content ): void {
        if( $this->encrypt ) {
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
        if( $this->encrypt ) {
            return StringHelper::decrypt($this->getContent());
        }
        return $this->getContent();
    }

}
