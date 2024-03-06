<?php

namespace controller\admin;

use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;

/**
 * @see \lib\core\blueprints\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("cache")]
class CacheFileController extends AController {

	/**
	 * Get a List of all cache files
	 *
	 * @return \lib\core\blueprints\AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("/", RequestMethod::GET)]
	public function index(): AResponse {
		$cache_infos = array();
		$this->getCacheFiles(PATH_CACHE_ROOT, $cache_infos);

		$view = new Template(PATH_VIEWS . "cache/index.html");
		TemplateData::set("cache_infos", $cache_infos);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->render());

		return new HtmlResponse($template->render());
	}

	/**
	 * @return AResponse
	 */
	#[Route("/delete", RequestMethod::DELETE)]
	public function deleteSubmit(): AResponse {
		$response = new HtmlResponse();
		$this->deleteCacheFiles(PATH_CACHE_ROOT);
		redirect("/cache");
		return $response;
	}

	/**
	 * Reads the given path recursive and collects all files
	 * they will be stored in the given file_list array
	 *
	 * @param string $path
	 * @param array $cache_stats
	 *
	 * @return void
	 */
	private function getCacheFiles(string $path, array &$cache_stats): void {
		if( !array_key_exists("total_files", $cache_stats) ) {
			$cache_stats["total_files"] = 0;
		}
		if( !array_key_exists("total_size", $cache_stats) ) {
			$cache_stats["total_size"] = 0;
		}

		$files = scandir($path);
		foreach( $files as $f ) {
			if( $f === "." || $f === ".." ) {
				continue;
			}
			$file_name = $path . $f;
			if( is_file($file_name) ) {
				$cache_stats["total_files"]++;
				$cache_stats["total_size"] += filesize($file_name);
			} elseif( is_dir($file_name) ) {
				$this->getCacheFiles($file_name . DIRECTORY_SEPARATOR, $cache_stats);
			}
		}
	}

	/**
	 * Deletes all files in the given path
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	private function deleteCacheFiles(string $path): void {
		$files = scandir($path);
		foreach( $files as $f ) {
			if( $f === "." || $f === ".." ) {
				continue;
			}
			$file_name = $path . $f;
			if( is_file($file_name) ) {
				unlink($file_name);
			} elseif( is_dir($file_name) ) {
				$this->deleteCacheFiles($file_name . DIRECTORY_SEPARATOR);
			}
		}
	}

}
