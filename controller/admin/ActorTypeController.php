<?php

namespace controller\admin;

use JsonException;
use models\ActorTypeModel;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\attributes\Route;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;
use system\System;

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
    #[Route("/", HTTP_GET)]
    public function index(): AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");

        $view = new Template(PATH_VIEWS."actor_types/index.html");
        $view->set('result_list', ActorTypeModel::find());

        $template->set("navigation", System::$Core->menu);
        $template->set("view", $view->parse());
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @return AResponse
     *
     * @throws JsonException
     * @throws SystemException
     */
    #[Route("create", HTTP_GET)]
    public function create(): AResponse {
        if( !System::$Core->actor_role->canCreateAll() ) {
            redirect("/error/403");
        }
        if( isset($_POST['cancel']) ) {
            redirect("/actor-types");
        }
        if( isset($_POST['create']) ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $actor_type = new ActorTypeModel();
                $actor_type->name = $_POST["name"];
                $actor_type->create();
                redirect("/actor-types");
            }
        }

        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);

        $view = new Template(PATH_VIEWS."actor_types/create.html");

        $template->set("view", $view->parse());
        $response->setOutput($template->parse());
        return $response;
    }


    /**
     *
     * @param ActorTypeModel $actor_type
     *
     * @return AResponse
     *
     * @throws SystemException
     * @throws JsonException
     */
    #[Route("/{actor_type}", HTTP_GET)]
    public function update ( ActorTypeModel $actor_type): AResponse {
        if( !System::$Core->actor_role->canUpdate($actor_type->id) ) {
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

        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);

        $view = new Template(PATH_VIEWS."actor_types/edit.html");
        $view->set("actor_type", $actor_type);

        $template->set("view", $view->parse());
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @param ActorTypeModel $actor_type
     *
     * @return AResponse
     *
     * @throws JsonException
     * @throws SystemException
     */
    #[Route("delete/{actor_type}", HTTP_GET)]
    public function delete(ActorTypeModel $actor_type): AResponse {
        if( !System::$Core->actor_role->canDelete($actor_type->id) ) {
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
     * Checks if all required values are set
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
