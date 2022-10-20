<?php

namespace controller\admin;

use system\abstracts\AResponse;
use system\abstracts\AController;
use system\classes\CacheFile;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;

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
		$this::$_menu->insertMenuItem(600, null, "Cache Files", "/cache");
	}

	/**
	 * @inheritDoc
	 */
	public function getRoutes(): array {
		return array(
			"/cache" => array("controller" => __CLASS__, "method" => "index")
		);
	}

	/**
	 * @inheritDoc
	 */
	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");

        $file_list = array();
        $this->getCacheFiles(PATH_CACHE_ROOT, $file_list);

		$template->set("navigation", $this::$_menu);
		$template->set("cache_list", $file_list);
		$template->set("view", new Template(PATH_VIEWS."cache/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

    /**
     * Reads the given path recursive and collects all files
     * they will be stored in the given file_list array
     *
     * @param string $path
     * @param array $file_list
     *
     * @return void
     */
    public function getCacheFiles( string $path, array &$file_list ): void {
        $files = scandir($path);
        foreach( $files as $f ) {
            if( $f === "." || $f === ".." ) {
                continue;
            }
            $file_name = $path.$f;
            if( is_file($file_name) ) {
                $cache_file = new CacheFile("", "");
                $cache_file->load($file_name);
                $file_list[$file_name] = $cache_file->loadFromCache();
            } elseif(is_dir($file_name) ) {
                $this->getCacheFiles($file_name.DIRECTORY_SEPARATOR, $file_list);
            }
        }
    }

}
