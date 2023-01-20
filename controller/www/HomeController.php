<?php

namespace controller\www;

use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\responses\HtmlResponse;
use lib\classes\Template;
use lib\exceptions\SystemException;

/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("/")]
class HomeController extends AController {

    /**
     * Shows the landing page
     *
     * @return AResponse
     *
     * @throws SystemException
     */
	#[Route("")]
    public function index(): AResponse {
        $view = new Template(PATH_VIEWS."home/index.html");

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}


    #[Route("//test/")]
    public function test() {

    }

	public function __toString(): string {
		return __CLASS__;
	}


}
