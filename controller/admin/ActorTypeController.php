<?php

namespace controller\admin;

use DateTime;
use JsonException;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use models\ActorTypeModel;
use system\exceptions\SystemException;

class ActorTypeController extends AController {

    /**
     * @inheritDoc
     */
    public function init( Router $router ): void {
        // Add routes to router
        $routes = $this->getRoutes();
        foreach( $routes as $url => $route ) {
            $router->addRoute($url, $route["controller"], $route["method"] );
        }

        // Add MenuItems to the Menu
        $this::$_menu->insertMenuItem(220, 200, "Benutzer-Typen", "/actor-types");
        $this::$_menu->insertMenuItem(230, 220, "Benutzer-Typ erstellen", "/actor-types/create");
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/actor-types" => array("controller" => __CLASS__, "method" => "index"),
            "/actor-types/{type}" => array("controller" => __CLASS__, "method" => "update"),
            "/actor-types/create" => array("controller" => __CLASS__, "method" => "create"),
            "/actor-types/delete/{type}" => array("controller" => __CLASS__, "method" => "delete")
        );
    }

    /**
     * @return AResponse
     *
     * @throws SystemException
     * @throws JsonException
     */
    public function index(): AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");

        $view = new Template(PATH_VIEWS."actor_types/index.html");
        $view->set('result_list', ActorTypeModel::find());

        $template->set("navigation", $this::$_menu);
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
    public function create(): AResponse {
        if( !$this::$_actor_role->canCreateAll() ) {
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
        $template->set("navigation", $this::$_menu);

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
    public function update ( ActorTypeModel $actor_type): AResponse {
        if( !$this::$_actor_role->canUpdate($actor_type->id) ) {
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
        $template->set("navigation", $this::$_menu);

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
    public function delete(ActorTypeModel $actor_type): AResponse {
        if( !$this::$_actor_role->canDelete($actor_type->id) ) {
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
