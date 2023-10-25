<?php

namespace controller\www;

use DateTime;
use Exception;
use lib\App;
use lib\classes\GeoCoordinate;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\helper\DateHelper;
use lib\helper\GeoHelper;
use lib\helper\MathHelper;


#[Route("tests")]
class TestController extends AController {

	/**
	 * Shows the starting page of the test controller
	 *
	 * @throws SystemException
	 */
	#[Route("/", RequestMethod::GET)]
	public function index(): AResponse {
		$view = new Template(PATH_VIEWS . "tests/index.html");

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("charts", RequestMethod::GET)]
	public function charts(): AResponse {
		$view = new Template(PATH_VIEWS . "tests/charts.html");

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("validator", RequestMethod::GET)]
	public function validator(): AResponse {
		$view = new Template(PATH_VIEWS . "tests/validator.html");

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("validator", RequestMethod::POST)]
	public function validatorSubmit(): AResponse {
		$view = new Template(PATH_VIEWS . "tests/validator.html");

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("math", RequestMethod::GET)]
	public function math(): AResponse {
		$view = new Template(PATH_VIEWS . "tests/math.html");
		TemplateData::set("distance", null);
		TemplateData::set("timespan", null);
		TemplateData::set("currency", null);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 * @return AResponse
	 * @throws SystemException
	 */
	#[Route("math", RequestMethod::POST)]
	public function mathSubmit(): AResponse {
		$distance = null;
		if( App::$request->contains("distance") ) {
			$coords = App::$request->get("coord");
			$long1 = (float)$coords[0]["long"];
			$lat1 = (float)$coords[0]["lat"];
			$long2 = (float)$coords[1]["long"];
			$lat2 = (float)$coords[1]["lat"];
			$distance = GeoHelper::getDistanceBetween(new GeoCoordinate($long1, $lat1), new GeoCoordinate($long2, $lat2));
			$distance = GeoHelper::getFormattedDistance($distance);
		}

		$timespan = null;
		if( App::$request->contains("timespan") ) {
			try {
				$start_date = new DateTime(App::$request->get("start_date"));
				$end_date = new DateTime(App::$request->get("end_date"));
				$timespan = DateHelper::getTimeBetween($start_date, $end_date);
			} catch( Exception $e ) {
				throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}

		$currency = null;
		if( App::$request->contains("currency") ) {
			try {
				$currency = MathHelper::getRoundedCurrency(App::$request->get("numeric_value"));
			} catch( Exception $e ) {
				throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}

		$view = new Template(PATH_VIEWS . "tests/math.html");
		TemplateData::set("distance", $distance);
		TemplateData::set("timespan", $timespan);
		TemplateData::set("currency", $currency);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

}
