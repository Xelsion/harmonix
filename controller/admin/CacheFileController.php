<?php

namespace controller\admin;

use models\Actor;
use system\abstracts\AResponse;
use system\abstracts\AController;
use system\classes\CacheFile;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;

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
		$this::$_menu->insertMenuItem(500, null, "Cache Files", "/cache");
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

		$cache_file_list = array();
		$files = scandir(PATH_CACHE);
		foreach( $files as $f ) {
			$file_name = PATH_CACHE.$f;
			if( is_file($file_name) ) {
				$cache_file = new CacheFile("");
				$cache_file->load($file_name);
				$cache_file_list[$file_name] = unserialize($cache_file->loadFromCache(), array(false));
			}
		}

		$template->set("navigation", $this::$_menu);
		$template->set("cache_list", $cache_file_list);
		$template->set("view", new Template(PATH_VIEWS."cache/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}
}