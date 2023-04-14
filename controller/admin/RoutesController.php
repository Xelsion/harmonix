<?php
namespace controller\admin;

use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\response_types\HtmlResponse;
use lib\core\Router;

#[Route("routes")]
class RoutesController Extends AController {

    /**
     * Get a list of all Routes
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("")]
    public function index(): AResponse {
        $view = new Template(PATH_VIEWS."routes/index.html");
        $view->set("routes_list", App::getInstanceOf(Router::class)->getSortedRoutes());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }
}
