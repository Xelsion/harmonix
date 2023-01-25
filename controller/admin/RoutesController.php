<?php
namespace controller\admin;

use lib\App;
use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\responses\HtmlResponse;
use lib\classes\Template;
use lib\core\Router;

use lib\exceptions\SystemException;

#[Route("routes")]
class RoutesController Extends AController {

    /**
     * Get a list of all Routes
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("")]
    public function index(): AResponse {
        $view = new Template(PATH_VIEWS."routes/index.html");
        $view->set("routes_list", App::getInstance(Router::class)->getSortedRoutes());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }
}
