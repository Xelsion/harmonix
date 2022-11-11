<?php

namespace controller\admin;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\System;


/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class CacheFileController extends AController {

	/**
	 * @inheritDoc
	 */
	public function init( Router $router ): void {
		// Add routes to router
		$routes = $this->getRoutes();
		foreach( $routes as $url => $route ) {
			$router->addRoute($url, $route["controller"], $route["method"] );
		}

		// Add MenuItems to the Menu
        System::$Core->menu->insertMenuItem(600, null, "Cache Files", "/cache");
	}

	/**
	 * @inheritDoc
	 */
	public function getRoutes(): array {
		return array(
			"/cache" => array("controller" => __CLASS__, "method" => "index"),
            "/cache/delete" => array("controller" => __CLASS__, "method" => "delete")
		);
	}

	/**
	 * @inheritDoc
	 */
	public function index(): AResponse {
		$response = new HtmlResponse();

        $cache_infos = array();
        $this->getCacheFiles(PATH_CACHE_ROOT, $cache_infos);

        $view = new Template(PATH_VIEWS."cache/index.html");
        $view->set("cache_infos", $cache_infos);
        $view_content = $view->parse();

        $template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", System::$Core->menu);
		$template->set("view", $view_content);
		$response->setOutput($template->parse());
		return $response;
	}

    /**
     * @return AResponse
     */
    public function delete(): AResponse {
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
    private function getCacheFiles( string $path, array &$cache_stats ): void {
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
            $file_name = $path.$f;
            if( is_file($file_name) ) {
                $cache_stats["total_files"]++;
                $cache_stats["total_size"] += filesize($file_name);
            } elseif(is_dir($file_name) ) {
                $this->getCacheFiles($file_name.DIRECTORY_SEPARATOR, $cache_stats);
            }
        }
    }

    private function deleteCacheFiles(string $path) {
        $files = scandir($path);
        foreach( $files as $f ) {
            if( $f === "." || $f === ".." ) {
                continue;
            }
            $file_name = $path.$f;
            if( is_file($file_name) ) {
                unlink($file_name);
            } elseif(is_dir($file_name) ) {
                $this->deleteCacheFiles($file_name.DIRECTORY_SEPARATOR, $cache_stats);
            }
        }
    }

}
