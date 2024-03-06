<?php

namespace controller\admin;

use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;

#[Route("config")]
class ConfigController extends AController {


	/**
	 * @throws SystemException
	 */
	#[Route("/", RequestMethod::GET)]
	public function index(): AResponse {
		$config_data = App::$config->getConfig();

		$view = new Template(PATH_VIEWS . "config/index.html");
		TemplateData::set("config_data", $config_data);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->render());

		return new HtmlResponse($template->render());
	}

	/**
	 * @throws SystemException
	 */
	#[Route("/", RequestMethod::POST)]
	public function indexSubmit(): AResponse {
		$config_data = App::$request->get('config');
		App::$config->setConfig($config_data);;
		App::$config->writeConfig();
		return $this->index();
	}

}