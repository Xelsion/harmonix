<?php
namespace controller\admin;

use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Configuration;
use lib\core\classes\Template;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use models\ActorTypeModel;
use repositories\ActorTypeRepository;

#[Route("actor-types")]
class ActorTypeController extends AController {


    private readonly ActorTypeRepository $type_repository;

    /**
     * @param ActorTypeRepository $type_repository
     * @param Configuration $config
     */
    public function __construct(ActorTypeRepository $type_repository,
                                Configuration       $config
    ) {
        parent::__construct($config);
        $this->type_repository = $type_repository;
    }

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
        $view->set('result_list', $this->type_repository->getAll());

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

        if( App::$request->contains('cancel') ) {
            redirect("/actor-types");
        }

        if( App::$request->contains("create") ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $actor_type = App::getInstanceOf(ActorTypeModel::class);
                $actor_type->name = App::$request->get("name");
                $this->type_repository->createObject($actor_type);
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

        if( App::$request->contains('cancel') ) {
            redirect("/actor-types");
        }

        if( App::$request->contains('update') ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $actor_type->name = App::$request->get('name');
                $this->type_repository->updateObject($actor_type);
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

        if( App::$request->contains('cancel') ) {
            redirect("/actors");
        }
        $this->type_repository->deleteObject($actor_type);
        redirect("/actor-types");
        return new HtmlResponse();
    }

    /**
     * Checks if all required values are setClass
     *
     * @return bool
     */
    private function postIsValid(): bool {
        if( !App::$request->contains('name') || App::$request->get('name') === "" ) {
            return false;
        }
        return true;
    }
}
