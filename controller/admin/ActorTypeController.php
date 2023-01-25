<?php
namespace controller\admin;

use lib\App;
use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\responses\HtmlResponse;
use lib\classes\Template;
use models\ActorTypeModel;

use lib\exceptions\SystemException;

#[Route("actor-types")]
class ActorTypeController extends AController {

    /**
     * Get a list of all actor types
     *
     * @return AResponse
     *
     * @throws SystemException
     * @throws JsonException
     */
    #[Route("")]
    public function index(): AResponse {
        $view = new Template(PATH_VIEWS."actor_types/index.html");
        $view->set('result_list', ActorTypeModel::find());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("create")]
    public function create(): AResponse {
        if( !App::$curr_actor_role->canCreateAll() ) {
            redirect("/error/403");
        }

        if( isset($_POST['cancel']) ) {
            redirect("/actor-types");
        }

        if( isset($_POST['create']) ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $actor_type = App::getInstance(ActorTypeModel::class);
                $actor_type->name = $_POST["name"];
                $actor_type->create();
                redirect("/actor-types");
            }
        }

        $view = new Template(PATH_VIEWS."actor_types/create.html");

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }


    /**
     *
     * @param ActorTypeModel $actor_type
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("/{actor_type}")]
    public function update ( ActorTypeModel $actor_type): AResponse {
        if( !App::$curr_actor_role->canUpdate($actor_type->id) ) {
            redirect("/error/403");
        }

        if( isset($_POST['cancel']) ) {
            redirect("/actor-types");
        }

        if( isset($_POST['update']) ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $actor_type->name = $_POST["name"];
                $actor_type->update();
                redirect("/actor-types");
            }
        }

        $view = new Template(PATH_VIEWS."actor_types/edit.html");
        $view->set("actor_type", $actor_type);
        $view->set("actor_role", App::$curr_actor_role);

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @param ActorTypeModel $actor_type
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("delete/{actor_type}")]
    public function delete(ActorTypeModel $actor_type): AResponse {
        if( !App::$curr_actor_role->canDelete($actor_type->id) ) {
            redirect("/error/403");
        }

        if( isset($_POST['cancel']) ) {
            redirect("/actors");
        }

        $actor_type->delete();
        redirect("/actor-types");
        return new HtmlResponse();
    }

    /**
     * Checks if all required values are setClass
     *
     * @return bool
     */
    private function postIsValid(): bool {
        if( !isset($_POST["name"]) || $_POST["name"] === "" ) {
            return false;
        }
        return true;
    }
}
