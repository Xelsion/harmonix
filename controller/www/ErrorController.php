<?php

namespace controller\www;

use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\responses\HtmlResponse;
use lib\classes\Template;
use lib\core\System;
use lib\exceptions\SystemException;

/**
 * @see \lib\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("error")]
class ErrorController extends AController {

    private array $_error_codes = array(403, 404);

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
                System::$Storage->set("title","400 - Bad Request");
                break;
            case 401:
                System::$Storage->set("title","401 - Unauthorized");
                break;
            case 403:
                System::$Storage->set("title","403 - Forbidden");
                break;
            case 404:
                System::$Storage->set("title","404 - Not Found");
                break;
            case 500:
                System::$Storage->set("title","500 - Internal Server Error");
                break;
        }

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", $view->parse());

        $response = new HtmlResponse($template->parse());
        $response->status_code = $error_code;
        return $response;
    }
}
