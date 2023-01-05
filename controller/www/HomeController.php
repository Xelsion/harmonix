<?php

namespace controller\www;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\attributes\Route;
use system\classes\responses\HtmlResponse;
use system\classes\Template;
use system\exceptions\SystemException;

/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("")]
class HomeController extends AController {

    /**
     * Shows the landing page
     *
     * @return AResponse
     *
     * @throws SystemException
     */
	#[Route("/", HTTP_GET)]
    public function index(): AResponse {
		$response = new HtmlResponse();
		$template = new Template(PATH_VIEWS."template.html");
        $template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}


}
