<?php
namespace controller\www;

use lib\App;
use lib\classes\Template;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\core\response_types\JsonResponse;
use PDO;

#[Route("tests")]
class TestController extends AController {

    /**
     * Shows the starting page of the test controller
     *
     * @throws \lib\core\exceptions\SystemException
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
     * @throws \lib\core\exceptions\SystemException
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
	 * @throws \lib\core\exceptions\SystemException
	 */
    #[Route("tinymce")]
    public function tinymce() : AResponse {
        $view = new Template(PATH_VIEWS . "tests/tinymce.html");

		$template = new Template(PATH_VIEWS . "template.html");
		$template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

    /**
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("validator")]
    public function validator() : AResponse {
        $view = new Template(PATH_VIEWS . "tests/validator.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return AResponse
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("chat")]
    public function chat() : AResponse {
        $view = new Template(PATH_VIEWS . "tests/chat.html");

        $template = new Template(PATH_VIEWS . "template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return AResponse
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("chat/post")]
    public function chatPost() : AResponse {
        try {
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $sql = "INSERT INTO chat (actor_id, message) VALUES (:actor_id, :message)";
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':actor_id', App::$curr_actor->id, PDO::PARAM_INT);
            $pdo->bindParam(':message', App::$request->data->get("msg"));
            $pdo->execute();
            $msg = array('status' => 'success', 'message' => 'Ok');
            $response = new JsonResponse();
            $response->setOutput($msg);
            return $response;
        } catch (\PDOException $e) {
            $msg = array('status' => 'failed', 'message' => $e->getMessage());
            $response = new JsonResponse();
            $response->setOutput($msg);
            return $response;
        }
    }

    /**
     * @return AResponse
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("chat/read")]
    public function chatRead() : AResponse {
        $last_id = 0;
        header("Content-Type: text:event-stream");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        $cm = App::getInstanceOf(ConnectionManager::class);
        $pdo = $cm->getConnection("mvc");
        $sql = "SELECT * FROM chat WHERE created>=DATE_SUB(NOW(), INTERVAL 2 SECOND) AND id>:last_id";
        while(true) {
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':last_id', $last_id, PDO::PARAM_INT);
            $results = $pdo->execute()->fetchAll();
            $result_count = count($results);
            ob_start();
            if( $result_count > 0 ) {
                echo "event: newText\n";
                echo "data: ".json_encode($results);
                echo "\n\n";
                $last_id = $results[$result_count-1]["id"];
            }
            ob_end_flush();
            flush();
            session_write_close();

            if( connection_aborted() ) {
                break;
            }
            usleep(1000);
        }

        return new JsonResponse();
    }

}
