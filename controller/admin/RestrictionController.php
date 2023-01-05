<?php

namespace controller\admin;

use Exception;
use JsonException;
use models\AccessRestrictionModel;
use models\AccessRestrictionTypeModel;
use models\ActorRoleModel;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\attributes\Route;
use system\classes\responses\HtmlResponse;
use system\classes\Router;
use system\classes\Template;
use system\exceptions\SystemException;
use system\System;

#[Route("restrictions")]
class RestrictionController extends AController {

    /**
     * Get a list of all restrictions
     *
     * @throws Exception
     */
    #[Route("/")]
    public function index(): AResponse {
        if( isset($_POST['update']) ) {
            $this->saveRestrictions();
        }

        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");

        $routes = System::$Core->router->getRoutes();
        $sorted_routes = array();
        foreach( $routes as $domain => $entries) {
            foreach( $entries as $path => $settings) {
                $controller = $settings["controller"];
                $method = $settings["method"];
                $sorted_routes[$domain][$controller][$method] = $path;
            }
        }

        $access_restrictions = AccessRestrictionModel::find();
        $current_restrictions = array();
        foreach( $access_restrictions as $restriction ) {
            $current_restrictions[$restriction->domain][$restriction->controller][$restriction->method] = array(
                "role" => $restriction->role_id,
                "type" => $restriction->restriction_type
            );
        }

        $template->set("navigation", System::$Core->menu);
        $template->set("view", new Template(PATH_VIEWS."restrictions/index.html"));
        $template->set("routes", $sorted_routes);
        $template->set("current_restrictions", $current_restrictions);
        $template->set("role_options", ActorRoleModel::find());
        $template->set("type_options", AccessRestrictionTypeModel::find());

        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @return AResponse
     * @throws SystemException|JsonException
     */
    #[Route("types")]
    public function types(): AResponse {
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", new Template(PATH_VIEWS."restrictions/types.html"));
        $template->set("type_list", AccessRestrictionTypeModel::find());
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @return AResponse
     * @throws JsonException
     * @throws SystemException
     */
    #[Route("types/create")]
    public function typesCreate(): AResponse {
        if( isset($_POST['cancel']) ) {
            redirect("/restrictions/types");
        }
        if( isset($_POST['create']) ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $type = new AccessRestrictionTypeModel();
                $type->name = $_POST["name"];
                $type->include_siblings = ($_POST["include_siblings"]) ? 1 :  0;
                $type->include_children = ($_POST["include_children"]) ? 1 :  0;
                $type->include_descendants = ($_POST["include_descendants"]) ? 1 :  0;
                $type->create();
                redirect("/restrictions/types");
            }
        }
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", new Template(PATH_VIEWS."restrictions/types_create.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @param AccessRestrictionTypeModel $type
     * @return AResponse
     * @throws SystemException
     */
    #[Route("types/{type}")]
    public function typesUpdate( AccessRestrictionTypeModel $type ): AResponse {
        if( isset($_POST['cancel']) ) {
            redirect("/restrictions/types");
        }
        if( isset($_POST['update']) ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $type->name = $_POST["name"];
                $type->include_siblings = ($_POST["include_siblings"]) ? 1 :  0;
                $type->include_children = ($_POST["include_children"]) ? 1 :  0;
                $type->include_descendants = ($_POST["include_descendants"]) ? 1 :  0;
                $type->update();
                redirect("/restrictions/types");
            }
        }
        $response = new HtmlResponse();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", new Template(PATH_VIEWS."restrictions/types_edit.html"));
        $template->set("type", $type);
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * Save the restrictions
     *
     * @return void
     * @throws JsonException
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
     * Checks if all required values are set
     *
     * @return bool
     */
    private function postIsValid(): bool {
        $is_valid = true;
        if( !isset($_POST["name"]) || $_POST["name"] === "" ) {
            $is_valid = false;
        }
        return $is_valid;
    }

}
