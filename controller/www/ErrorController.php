<?php

namespace controller\www;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;

/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ErrorController extends AController {

    private array $_error_codes = array(403, 404);

    /**
     * @inheritDoc
     */
	public function init( Router $router ): void {
        // Add routes to router
        $routes = $this->getRoutes();
        foreach( $routes as $url => $route ) {
            $router->addRoute($url, $route["controller"], $route["method"] );
        }
	}

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/error" => array("controller" => __CLASS__, "method" => "index"),
            "/error/{error_code}" => array("controller" => __CLASS__, "method" => "error")
        );
    }

    /**
     * @inheritDoc
     */
	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", $this::$_menu);
		$template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

    /**
     * @param int $error_code
     * @return AResponse
     */
	public function error( int $error_code ): AResponse {
		$response = new ResponseHTML();
        if( !in_array($error_code, $this->_error_codes, true )) {
            $error_code = 404;
        }
		$response->status_code = $error_code;
		$template = new Template(PATH_VIEWS."template.html");
        $view = new Template(PATH_VIEWS."error/".$error_code.".html");
		$template->set("navigation", $this::$_menu);
		$template->set("view", $view->parse());
		$response->setOutput($template->parse());
		return $response;
	}
}