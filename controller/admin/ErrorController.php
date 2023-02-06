<?php
namespace controller\admin;

use lib\classes\Template;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;

/**
 * @see \lib\core\blueprints\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("error")]
class ErrorController extends AController {

    /**
     *  Shows the error page with the given code
     *
     * @param int $error_code
     *
     * @return AResponse
     * @throws SystemException
     */
    #[Route("{error_code}")]
    public function error( int $error_code ): AResponse {
		$view = new Template(PATH_VIEWS."error/display.html");
        switch( $error_code ) {
            case 400:
                $view->set("title","400 Bad Request");
                break;
            case 401:
                $view->set("title","401 Unauthorized");
                break;
            case 403:
                $view->set("title","403 Forbidden");
                break;
            case 404:
                $view->set("title","404 Not Found");
                break;
            case 500:
                $view->set("title","500 Internal Server Error");
                break;
        }


		$template = new Template(PATH_VIEWS."template.html");
		$template->set("view", $view->parse());

        $response = new HtmlResponse();
        $response->status_code = $error_code;
        $response->setOutput($template->parse());
		return $response;
	}
}
