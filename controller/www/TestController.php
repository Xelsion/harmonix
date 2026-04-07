<?php

namespace controller\www;

use DateTime;
use Exception;
use lib\App;
use lib\classes\GeoCoordinate;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\File;
use lib\core\classes\LinqList;
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
		TemplateData::set("view", $view->parse(), true);

		return new HtmlResponse($template);
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
		TemplateData::set("view", $view->parse(), true);

		return new HtmlResponse($template);
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
		TemplateData::set("view", $view->parse(), true);

		return new HtmlResponse($template);
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
		TemplateData::set("view", $view->parse(), true);

		return new HtmlResponse($template);
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
		TemplateData::set("view", $view->parse(), true);

		return new HtmlResponse($template);
	}

	/**
	 * @return AResponse
	 * @throws SystemException
	 */
	#[Route("math", RequestMethod::POST)]
	public function mathSubmit(): AResponse {
		$distance = null;
		$coords = (App::$request->contains("coords")) ? App::$request->get("coords") : array();
		if( !empty($coords) && App::$request->contains("distance") ) {
			$long1 = (float)$coords[0]["long"];
			$lat1 = (float)$coords[0]["lat"];
			$long2 = (float)$coords[1]["long"];
			$lat2 = (float)$coords[1]["lat"];
			$distance = GeoHelper::getDistanceBetween(new GeoCoordinate($long1, $lat1), new GeoCoordinate($long2, $lat2));
			$distance = GeoHelper::getFormattedDistance($distance);
		}

		try {
			$timespan = null;
			$start_date = (App::$request->contains("start_date")) ? new DateTime(App::$request->get("start_date")) : null;
			$end_date = (App::$request->contains("end_date")) ? new DateTime(App::$request->get("end_date")) : null;
			if( $start_date !== null && $end_date !== null ) {
				$timespan = DateHelper::getTimeBetween($start_date, $end_date);
				$start_date = $start_date->format("Y-m-d");
				$end_date = $end_date->format("Y-m-d");
			}
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}

		try {
			$currency = null;
			$numeric_value = (App::$request->contains("numeric_value")) ? App::$request->get("numeric_value") : null;
			if( $numeric_value !== null ) {
				$currency = MathHelper::getRoundedCurrency($numeric_value);
			}
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}

		$view = new Template(PATH_VIEWS . "tests/math.html");
		TemplateData::set("coords", $coords);
		TemplateData::set("distance", $distance);
		TemplateData::set("timespan", $timespan);
		TemplateData::set("start_date", $start_date);
		TemplateData::set("end_date", $end_date);
		TemplateData::set("numeric_value", $numeric_value);
		TemplateData::set("currency", $currency);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse(), true);

		return new HtmlResponse($template);
	}

	/**
	 * @return AResponse
	 * @throws SystemException
	 */
	#[Route("base64", RequestMethod::GET, RequestMethod::POST)]
	public function File2Base64(): AResponse {
		if( App::$request->contains("upload") ) {
			$upload = App::$request->get("upload");

			$file = new File($upload["tmp_name"]);
			$base64 = base64_encode($file->getContent());
			TemplateData::set("base64", $base64);
		}
		$view = new Template(PATH_VIEWS . "tests/base64.html");
		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse(), true);

		return new HtmlResponse($template);
	}

	/**
	 * @return AResponse
	 * @throws SystemException
	 */
	#[Route("linq", RequestMethod::GET, RequestMethod::POST)]
	public function linq(): AResponse {
		$min = 42;
		$max = 63;
		$ll = new LinqList();
		for( $i = 0; $i < 100; $i++ ) {
			$ll->add($i + 1);
		}
		$results = $ll->where(fn($e) => $e < $max && $e > $min)->select(fn($e) => $e . " Jahre")->distinct()->getAll();

		$view = new Template(PATH_VIEWS . "tests/linq.html");
		TemplateData::set("results", $results);
		TemplateData::set("view", $view->parse(), true);
		$template = new Template(PATH_VIEWS . "template.html");
		return new HtmlResponse($template);
	}

}
