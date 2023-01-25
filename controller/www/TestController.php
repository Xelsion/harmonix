<?php
namespace controller\www;

use lib\App;
use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\cache\ResponseCache;
use lib\classes\responses\HtmlResponse;
use lib\classes\Template;
use lib\helper\RequestHelper;
use models\ActorModel;

use lib\exceptions\SystemException;

#[Route("tests")]
class TestController extends AController {

    /**
     * Shows the starting page of the test controller
     *
     * @throws SystemException
     */
    #[Route("/")]
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
    #[Route("charts")]
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
    #[Route("actors")]
    public function actors() : AResponse {
        $params = RequestHelper::getPaginationParams();

        $cache = App::getInstance(ResponseCache::class);
        $cache->initCacheFor(__METHOD__, ...$params);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."template.html");
        $cache->addFileCheck(PATH_VIEWS."tests/actors.html");
        $cache->addDBCheck("mvc", "actors");
        if( self::$caching && $cache->isUpToDate() ) {
            $content = $cache->getContent();
        } else {
            $template = new Template(PATH_VIEWS . "template.html");
            $template->set("actor_list", ActorModel::find());
            $template->set("view", new Template(PATH_VIEWS . "tests/actors.html"));
            $content = $template->parse();

            // if caching is enabled write the generated output into the cache file
            if(self::$caching) {
                $cache->saveContent($content);
            }
        }

        return new HtmlResponse($content);
    }

    /**
     *
     * @param int $actor_id
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("actors/{actor_id}")]
    public function actorsDetail( int $actor_id ) : AResponse {
        $results = ActorModel::find([["id", "=", $actor_id]]);
        if( count($results) === 0 ) {
            redirect("/error/404");
        }

        $view =  new Template(PATH_VIEWS."tests/actors_detail.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("actor", $results[0]);
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

	/**
     *
	 * @return AResponse
     *
	 * @throws SystemException
	 */
    #[Route("tinymce")]
    public function tinymce() : AResponse {
		if( !empty($_POST) && isset($_POST["content"]) ) {
			print_debug($_POST["content"]);
		}

        $view = new Template(PATH_VIEWS . "tests/tinymce.html");

		$template = new Template(PATH_VIEWS . "template.html");
		$template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

}
