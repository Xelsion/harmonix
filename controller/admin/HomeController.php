<?php

namespace controller\admin;

use core\abstracts\AResponse;
use core\abstracts\AController;
use core\classes\responses\ResponseHTML;
use core\classes\Router;
use core\classes\Template;
use core\classes\tree\Menu;
use models\Actor;


/**
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HomeController extends AController {

	public function __construct() {

	}

	public function initRoutes( Router $router ): void {
		$router->addRoute("/", __CLASS__."->indexAction");
	}

	public function indexAction(): AResponse {
        $menu = new Menu();
        $menu->insertMenuItem(0,null, "Home", "/");
        $menu->insertMenuItem(4,0, "Hier und Dort", "/");
        $menu->insertMenuItem(5,0, "Dies und Das", "/");

        $menu->insertMenuItem(7,5, "Dies ist gut", "/");
        $menu->insertMenuItem(8,5, "Das könnte besser sein", "/");
        $menu->insertMenuItem(9,5, "Das behauptest Du", "/");


        $menu->insertMenuItem(6,0, "Dieses und Jenes", "/");


        $menu->insertMenuItem(1,null, "Benutzer", "/");
        $menu->insertMenuItem(2,null, "Rechte", "/");
        $menu->insertMenuItem(3,null, "Controller", "/");

		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $menu);
		$template->set("view", new Template(PATH_VIEWS."home/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}