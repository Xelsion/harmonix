<?php
namespace controller\admin;

use lib\App;
use lib\classes\Template;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\core\Router;
use models\AccessRestrictionModel;
use models\AccessRestrictionTypeModel;
use models\ActorRoleModel;

#[Route("restrictions")]
class RestrictionController extends AController {

    /**
     * Get a list of all restrictions
     *
     * @return \lib\core\blueprints\AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("")]
    public function index(): AResponse {
        if( isset($_POST['update']) ) {
            $this->saveRestrictions();
        }

        $access_restrictions = AccessRestrictionModel::find();
        $current_restrictions = array();
        foreach( $access_restrictions as $restriction ) {
            $current_restrictions[$restriction->domain][$restriction->controller][$restriction->method] = array(
                "role" => $restriction->role_id,
                "type" => $restriction->restriction_type
            );
        }

        $view = new Template(PATH_VIEWS."restrictions/index.html");
        $view->set("routes", App::getInstanceOf(Router::class)->getSortedRoutes());
        $view->set("current_restrictions", $current_restrictions);
        $view->set("role_options", ActorRoleModel::find());
        $view->set("type_options", AccessRestrictionTypeModel::find());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return \lib\core\blueprints\AResponse
     *
     * @throws SystemException
     */
    #[Route("types")]
    public function types(): AResponse {
        $view = new Template(PATH_VIEWS."restrictions/types.html");
        $view->set("type_list", AccessRestrictionTypeModel::find());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("types/create")]
    public function typesCreate(): AResponse {
        if( App::$request->data->contains("cancel") ) {
            redirect("/restrictions/types");
        }

        if( App::$request->data->contains("create") ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $type = new AccessRestrictionTypeModel();
                $type->name = App::$request->data->get("name");
                $type->include_siblings = (App::$request->data->get("include_siblings")) ? 1 :  0;
                $type->include_children = (App::$request->data->get("include_children")) ? 1 :  0;
                $type->include_descendants = (App::$request->data->get("include_descendants")) ? 1 :  0;
                $type->create();
                redirect("/restrictions/types");
            }
        }

        $view = new Template(PATH_VIEWS."restrictions/types_create.html");

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @param AccessRestrictionTypeModel $type
     *
     * @return \lib\core\blueprints\AResponse
     *
     * @throws SystemException
     */
    #[Route("types/{type}")]
    public function typesUpdate( AccessRestrictionTypeModel $type ): AResponse {
        if( App::$request->data->contains("cancel") ) {
            redirect("/restrictions/types");
        }

        if( App::$request->data->contains("update") ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $type->name = App::$request->data->get("name");
                $type->include_siblings = (App::$request->data->get("include_siblings")) ? 1 :  0;
                $type->include_children = (App::$request->data->get("include_children")) ? 1 :  0;
                $type->include_descendants = (App::$request->data->get("include_descendants")) ? 1 :  0;
                $type->update();
                redirect("/restrictions/types");
            }
        }

        $view = new Template(PATH_VIEWS."restrictions/types_edit.html");
        $view->set("type", $type);

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * Save the restrictions
     *
     * @return void
     *
     * @throws SystemException
     */
    private function saveRestrictions(): void {

        $restrictions = array();
        foreach( $_POST['restriction'] as $domain => $entry_domain ) {
            if( (int)$entry_domain["role"] > 0 && (int)$entry_domain["type"] > 0 ) {
                $restrictions[$domain][null][null] = array( $entry_domain["role"], $entry_domain["type"]);
            }
            foreach( $entry_domain["controller"] as $controller => $entry_controller ) {
                $controller = str_replace("-", "\\", $controller);
                if( (int)$entry_controller["role"] > 0 && (int)$entry_controller["type"] > 0 ) {
                    $restrictions[$domain][$controller][null] = array( $entry_controller["role"], $entry_controller["type"]);
                }
                foreach( $entry_controller["method"] as $method => $entry_method ) {
                    if( (int)$entry_method["role"] > 0 && (int)$entry_method["type"] > 0 ) {
                        $restrictions[$domain][$controller][$method] = array( $entry_method["role"], $entry_method["type"]);
                    }
                }
            }
        }

        AccessRestrictionModel::deleteAll();
        foreach($restrictions as $domain => $controllers) {
            foreach( $controllers as $controller => $methods ) {
                foreach( $methods as $method => $entry ) {
                    $restriction = new AccessRestrictionModel();
                    $restriction->domain = $domain;
                    $restriction->controller = ( $controller !== "") ? $controller : null;
                    $restriction->method = ( $method !== "") ? $method : null;
                    $restriction->role_id = $entry[0];
                    $restriction->restriction_type = $entry[1];
                    $restriction->create();
                }
            }
        }
    }

    /**
     * Checks if all required values are setClass
     *
     * @return bool
     */
    private function postIsValid(): bool {
        $is_valid = true;
        if( !App::$request->data->contains("name") || App::$request->data->get("name") === "" ) {
            $is_valid = false;
        }
        return $is_valid;
    }

}
