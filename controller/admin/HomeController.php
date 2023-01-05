<?php

namespace controller\admin;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\attributes\Route;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;
use system\System;

/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("/")]
class HomeController extends AController {

    /**
     *  Get the starting site
     */
    #[Route("/")]
    public function index(): AResponse {
		$response = new HtmlResponse();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", System::$Core->menu);
		$template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}
