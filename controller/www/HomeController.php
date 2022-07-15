<?php

namespace controller\www;

use system\abstracts\AResponse;
use system\abstracts\AController;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;

/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HomeController extends AController {

    /**
     * @param Router $router
     * @throws SystemException
     */
	public function init( Router $router ): void {
        // Add routes to router
        $routes = $this->getRoutes();
        foreach( $routes as $url => $route ) {
            $router->addRoute($url, $route["controller"], $route["method"] );
        }

        // Add MenuItems to the Menu
        $this::$_menu->insertMenuItem(100, null, "Home", "/");
	}

    /**
     * @return array[]
     */
    public function getRoutes(): array {
        return array(
            "/" => array("controller" => __CLASS__, "method" => "index")
        );
    }

    /**
     * @return AResponse
     * @throws SystemException
     */
	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
        $template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}


}
