<?php
namespace controller\www;

use lib\App;
use lib\core\attributes\HttpGet;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\ConnectionManager;
use lib\core\response_types\HtmlResponse;
use lib\core\response_types\JsonResponse;
use PDO;

#[Route("tests")]
class TestController extends AController {

    /**
     * Shows the starting page of the test controller
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[HttpGet("/")]
    public function index(): AResponse {
        $view = new Template(PATH_VIEWS."tests/index.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[HttpGet("charts")]
    public function charts(): AResponse {
        $view = new Template(PATH_VIEWS."tests/charts.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse() );

        return new HtmlResponse($template->parse());
    }

	/**
     *
	 * @return AResponse
     *
	 * @throws \lib\core\exceptions\SystemException
	 */
    #[HttpGet("tinymce")]
    public function tinymce() : AResponse {
        $view = new Template(PATH_VIEWS . "tests/tinymce.html");

		$template = new Template(PATH_VIEWS . "template.html");
		$template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

    /**
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[HttpGet("validator")]
    public function validator() : AResponse {
        $view = new Template(PATH_VIEWS . "tests/validator.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

}
