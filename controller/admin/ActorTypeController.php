<?php
namespace controller\admin;

use lib\App;
use lib\classes\Template;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use models\ActorTypeModel;

#[Route("actor-types")]
class ActorTypeController extends AController {

    /**
     * Get a list of all actor types
     *
     * @return \lib\core\blueprints\AResponse
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
     * @return \lib\core\blueprints\AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("create")]
    public function create(): AResponse {
        if( !App::$curr_actor_role->canCreateAll() ) {
            redirect("/error/403");
        }

        if( App::$request->data->contains('cancel') ) {
            redirect("/actor-types");
        }

        if( App::$request->data->contains("create") ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $actor_type = App::getInstanceOf(ActorTypeModel::class);
                $actor_type->name = App::$request->data->get("name");
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
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("/{actor_type}")]
    public function update ( ActorTypeModel $actor_type): AResponse {
        if( !App::$curr_actor_role->canUpdate($actor_type->id) ) {
            redirect("/error/403");
        }

        if( App::$request->data->contains('cancel') ) {
            redirect("/actor-types");
        }

        if( App::$request->data->contains('update') ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $actor_type->name = App::$request->data->get('name');
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

        if( App::$request->data->contains('cancel') ) {
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
        if( !App::$request->data->contains('name') || App::$request->data->get('name') === "" ) {
            return false;
        }
        return true;
    }
}
