<?php

namespace controller\admin;

use Exception;
use JsonException;
use models\AccessRestrictionType;
use models\AccessRestriction;
use models\ActorRole;
use system\abstracts\AResponse;
use system\classes\CacheFile;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\abstracts\AController;
use system\classes\Template;
use system\Core;
use system\exceptions\SystemException;

class RestrictionController extends AController {

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
        $this::$_menu->insertMenuItem(400, null, "Zugriffsrechte", "/restrictions");
        $this::$_menu->insertMenuItem(410, 400, "Zugriffs Typen", "/restrictions/types");
        $this::$_menu->insertMenuItem(411, 410, "Type erstellen", "/restrictions/types/create");
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/restrictions" => array("controller" => __CLASS__, "method" => "index"),
            "/restrictions/types" => array("controller" => __CLASS__, "method" => "types"),
            "/restrictions/types/create" => array("controller" => __CLASS__, "method" => "typesCreate"),
            "/restrictions/types/{type}" => array("controller" => __CLASS__, "method" => "typesUpdate"),
            "/restrictions/types/delete/{type}" => array("controller" => __CLASS__, "method" => "typesDelete")
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function index(): AResponse {
        if( isset($_POST['update']) ) {
            $this->saveRestrictions();
        }

        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS."template.html");

        $routes = array();
        Core::$_router->getAllRoutes(PATH_CONTROLLER_ROOT, $routes);

        $access_restrictions = AccessRestriction::find();
        $current_restrictions = array();
        foreach( $access_restrictions as $restriction ) {
            $current_restrictions[$restriction->domain][$restriction->controller][$restriction->method] = array(
                "role" => $restriction->role_id,
                "type" => $restriction->restriction_type
            );
        }

        $template->set("navigation", $this::$_menu);
        $template->set("view", new Template(PATH_VIEWS."restrictions/index.html"));
        $template->set("routes", $routes);
        $template->set("current_restrictions", $current_restrictions);
        $template->set("role_options", ActorRole::find());
        $template->set("type_options", AccessRestrictionType::find());

        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @return AResponse
     * @throws SystemException|JsonException
     */
    public function types(): AResponse {
        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $this::$_menu);
        $template->set("view", new Template(PATH_VIEWS."restrictions/types.html"));
        $template->set("type_list", AccessRestrictionType::find());
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @return AResponse
     * @throws JsonException
     * @throws SystemException
     */
    public function typesCreate(): AResponse {
        if( isset($_POST['cancel']) ) {
            redirect("/restrictions/types");
        }
        if( isset($_POST['create']) ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $type = new AccessRestrictionType();
                $type->name = $_POST["name"];
                $type->include_siblings = ($_POST["include_siblings"]) ? 1 :  0;
                $type->include_children = ($_POST["include_children"]) ? 1 :  0;
                $type->include_descendants = ($_POST["include_descendants"]) ? 1 :  0;
                $type->create();
                redirect("/restrictions/types");
            }
        }
        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $this::$_menu);
        $template->set("view", new Template(PATH_VIEWS."restrictions/types_create.html"));
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @param AccessRestrictionType $type
     * @return AResponse
     * @throws SystemException
     */
    public function typesUpdate( AccessRestrictionType $type ): AResponse {
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
        $response = new ResponseHTML();
        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $this::$_menu);
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

        AccessRestriction::deleteAll();
        foreach($restrictions as $domain => $controllers) {
            foreach( $controllers as $controller => $methods ) {
                foreach( $methods as $method => $entry ) {
                    $restriction = new AccessRestriction();
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