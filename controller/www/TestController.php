<?php
namespace controller\www;

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
        $view = new Template(PATH_VIEWS."tests/index.html");

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
        $view = new Template(PATH_VIEWS."tests/charts.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse() );

        return new HtmlResponse($template->parse());
    }

    /**
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[HttpGet("validator")]
    public function validator() : AResponse {
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
    public function math() : AResponse {
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

        $view = new Template(PATH_VIEWS . "tests/math.html");
        $view->set("distance", $distance);

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

}
