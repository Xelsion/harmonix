<?php

namespace controller\www;

use JsonException;


use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;

use models\ActorModel;

class TestController extends AController {

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
        $this::$_menu->insertMenuItem(200, null, "Tests", "/tests");
        $this::$_menu->insertMenuItem(210, 200, "Actors", "/tests/actors");
        $this::$_menu->insertMenuItem(220, 200, "Charts", "/tests/charts");
	    $this::$_menu->insertMenuItem(230, 200, "TinyMCE", "/tests/tinymce");
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/tests" => array("controller" => __CLASS__, "method" => "index"),
            "/tests/actors" => array("controller" => __CLASS__, "method" => "actors"),
            "/tests/actors/{id}" => array("controller" => __CLASS__, "method" => "actorsDetail"),
            "/tests/charts" => array("controller" => __CLASS__, "method" => "charts"),
	        "/tests/tinymce" => array("controller" => __CLASS__, "method" => "tinymce")
        );
    }

    /**
     * @inheritDoc
     *
     * @throws SystemException
     */
    public function index(): AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", new Template(PATH_VIEWS . "tests/index.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @return AResponse
     *
     * @throws SystemException
     */
    public function charts(): AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", new Template(PATH_VIEWS."tests/charts.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     *
     * @throws SystemException
     * @throws JsonException
     */
    public function actors() : AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("actor_list", ActorModel::find());
        $template->set("view", new Template(PATH_VIEWS."tests/actors.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     *
     * @throws SystemException
     * @throws JsonException
     */
    public function actorsDetail( int $id ) : AResponse {
        $results = ActorModel::find([["id", "=", $id]]);
        if( count($results) === 0 ) {
            redirect("/error/404");
        }
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("actor", $results[0]);
        $template->set("view", new Template(PATH_VIEWS."tests/actors_detail.html"));
        $response->setOutput($template->parse());
        return $response;
    }

	/**
     *
	 * @return HtmlResponse
	 * @throws SystemException
	 */
	public function tinymce() : AResponse {
		if( !empty($_POST) && isset($_POST["content"]) ) {
			print_debug($_POST["content"]);
		}
		$response = new HtmlResponse();
		$template = new Template(PATH_VIEWS . "template.html");
		$template->set("view", new Template(PATH_VIEWS . "tests/tinymce.html"));
		$response->setOutput($template->parse());
		return $response;
	}

}
