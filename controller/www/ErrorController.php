<?php

namespace controller\www;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\attributes\Route;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;

/**
 * @see \system\abstracts\AController
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
    #[Route("{error_code}}")]
    public function error( int $error_code ): AResponse {
        $response = new HtmlResponse();
        $response->status_code = $error_code;
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", new Template(PATH_VIEWS."error/".$error_code.".html"));
        $response->setOutput($template->parse());
        return $response;
    }
}
