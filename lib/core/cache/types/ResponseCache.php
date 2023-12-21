<?php

namespace lib\core\cache\types;

use lib\App;
use lib\core\cache\CacheFile;
use lib\core\classes\File;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;

/**
 * The ResponseCache class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ResponseCache {

	private readonly ConnectionManager $connectionManager;
	private static ?ResponseCache $_instance = null;
	private CacheFile $cache;
	private array $db_checks = array();
	private array $file_checks = array();

	/**
	 * The class constructor
	 * will be called once by the static method getInstanceOf()
	 *
	 */
	public function __construct(ConnectionManager $connectionManager) {
		$this->connectionManager = $connectionManager;

		// Always check on these files
		$this->addFileCheck(__FILE__);
		$this->addFileCheck(PATH_ROOT . "lang-de.ini");
		$this->addFileCheck(PATH_ROOT . "functions.php");
		$this->addFileCheck(PATH_ROOT . "constants.php");
		$this->addFileCheck(PATH_LIB . "App.php");
		$this->addFileCheck(PATH_LIB . "core/Router.php");
		$this->addFileCheck(PATH_LIB . "core/Request.php");
		$this->addFileCheck(PATH_LIB . "core/ClassManager.php");
		$this->addFileCheck(PATH_LIB . "helper/HtmlHelper.php");
		$this->addFileCheck(PATH_LIB . "helper/RequestHelper.php");
		$this->addFileCheck(PATH_LIB . "helper/StringHelper.php");
		$this->addFileCheck(PATH_VIEWS . "template.html");
		$this->addFileCheck(PATH_VIEWS . "main_menu.html");

		// Always check on these tables
		$this->addDBCheck("mvc", "actors");
		$this->addDBCheck("mvc", "actor_roles");
		$this->addDBCheck("mvc", "access_permissions");
		$this->addDBCheck("mvc", "access_restrictions");
	}

	/**
	 * Initializes a cache file with a combination of the given name and the
	 * parameters
	 *
	 * @param string $cache_name
	 * @param ...$param
	 *
	 * @return void
	 *
	 * @throws SystemException
	 */
	public function initCacheFor(string $cache_name, ...$param): void {
		$entries = array();
		foreach( $param as $p ) {
			if( is_object($p) ) {
				$entries[] = serialize($p);
			} else {
				$entries[] = (string)$p;
			}
		}
		$this->cache = App::getInstanceOf(CacheFile::class, null, [
			"file_name" => $cache_name,
			"hash"      => implode("-", $entries)
		]);
	}

	/**
	 * Returns the content of the current cache file
	 *
	 * @return string
	 */
	public function getContent(): string {
		App::$storage->set("is_cached", true);
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
	public function saveContent(string $content): void {
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
	public function addDBCheck($db_name, $table_name): void {
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
	public function addFileCheck($file_name): void {
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
	 * @throws SystemException
	 */
	public function isUpToDate(): bool {
		if( !$this->cache->exists() ) {
			return false;
		}
		return ($this->doDBCheck() && $this->doFileChecks());
	}

	/**
	 * Reads the last modification date of all tables in the current checklist
	 *
	 * @return bool
	 *
	 * @throws SystemException
	 */
	private function doDBCheck(): bool {
		foreach( $this->db_checks as $dbname => $tables ) {
			$pdo = $this->connectionManager->getConnection($dbname);
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
			if( $file->exists() ) {
				$file_time = $file->getLastModified();
				if( $file_time > $cache_time ) {
					return false;
				}
			} else {
				echo "File " . $file_name . " doesn't exist<br />";
			}
		}
		return true;
	}

}
