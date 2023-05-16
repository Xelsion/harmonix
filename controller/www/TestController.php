<?php

namespace controller\www;

use DateTime;
use Exception;
use lib\App;
use lib\classes\GeoCoordinate;
use lib\core\attributes\HttpGet;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Template;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\helper\MathHelper;


#[Route("tests")]
class TestController extends AController {

    /**
     * Shows the starting page of the test controller
     *
     * @throws SystemException
     */
    #[HttpGet("/")]
    public function index(): AResponse {
        $view = new Template(PATH_VIEWS . "tests/index.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return AResponse
     *
     * @throws SystemException
     */
    #[HttpGet("charts")]
    public function charts(): AResponse {
        $view = new Template(PATH_VIEWS . "tests/charts.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("validator")]
    public function validator(): AResponse {
        $view = new Template(PATH_VIEWS . "tests/validator.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("math")]
    public function math(): AResponse {
        $distance = null;
        if( App::$request->contains("distance") ) {
            $coords = App::$request->get("coord");
            $long1 = (float)$coords[0]["long"];
            $lat1 = (float)$coords[0]["lat"];
            $long2 = (float)$coords[1]["long"];
            $lat2 = (float)$coords[1]["lat"];
            $distance = MathHelper::getDistanceBetween(new GeoCoordinate($long1, $lat1), new GeoCoordinate($long2, $lat2));
            $distance = MathHelper::getFormattedDistance($distance);
        }

        $timespan = null;
        if( App::$request->contains("timespan") ) {
            try {
                $start_date = new DateTime(App::$request->get("start_date"));
                $end_date = new DateTime(App::$request->get("end_date"));
                $timespan = MathHelper::getTimeBetween($start_date, $end_date);
            } catch( Exception $e ) {
                throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }

        $view = new Template(PATH_VIEWS . "tests/math.html");
        $view->set("distance", $distance);
        $view->set("timespan", $timespan);

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

}
