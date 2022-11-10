<?php

namespace system\classes\cache;

use DateTime;
use Exception;
use JsonException;

use system\Core;
use system\classes\CacheFile;
use system\classes\File;
use system\classes\Template;
use system\exceptions\SystemException;


/**
 * The TemplateCache class
 * Can be used the store parsed template content in a cache file
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class TemplateCache {

    private Template $tpl;
    public CacheFile $cache;
    private int $last_modified;

    /**
     * The class constructor
     *
     * @param Template $tpl - The template instance
     * @param ...$param - 0 to many arguments that will be added to the cache filename
     */
    public function __construct(Template $tpl, ...$param) {
        $this->tpl = $tpl;
        $this->cache = new CacheFile($tpl->getFilePath() . implode("-", $param));
    }

    /**
     * Reads the last modification date for the given table ind the given db
     *
     * @param string $db
     * @param string $table_name
     *
     * @return void
     *
     * @throws SystemException
     * @throws JsonException
     * @throws Exception
     */
    public function checkTable( string $db, string $table_name ): void {
        $modified = 0;
        $pdo = Core::$_connection_manager->getConnection($db, false);
        $pdo->prepare("SELECT create_time, update_time FROM information_schema.tables WHERE table_schema=:db AND table_name=:table");
        $pdo->bindParam("db", $db);
        $pdo->bindParam("table", $table_name);
        $row = $pdo->execute()->fetch();
        if( $row ) {
            if ( !is_null($row["update_time"]) ) {
                $last_update = new DateTime($row["update_time"]);
            } else {
                $last_update = new DateTime($row["create_time"]);
            }
            $modified = $last_update->getTimestamp();
        }
        $this->last_modified = $modified;
    }

    /**
     * Saves the given content to the cache file
     *
     * @param string $content
     *
     * @return void
     *
     * @throws SystemException
     */
    public function saveContent(string $content): void {
        $this->cache->saveToCache($content);
    }

    /**
     * Returns the cache file content
     *
     * @return string
     */
    public function getContent(): string {
        return $this->cache->getContent();
    }

    /**
     * Checks if the cache file is up-to-date
     *
     * @return bool
     */
    public function isUpToDate(): bool {
        $f = new File($this->tpl->getFilePath());
        $tpl_age = $f->getLastModified();
        $cache_age = $this->cache->getLastModified();
        return ($cache_age > $tpl_age && $cache_age > $this->last_modified && $this->cache->exists());
    }

}
