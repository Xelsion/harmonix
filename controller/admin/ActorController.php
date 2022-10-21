<?php

namespace controller\admin;

use DateTime;
use Exception;
use JsonException;

use PDO;
use system\Core;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\Router;
use system\classes\Template;
use system\classes\responses\ResponseHTML;
use system\helper\HtmlHelper;
use system\helper\RequestHelper;
use system\exceptions\SystemException;

use models\Actor;
use models\ActorRole;
use models\AccessPermission;

/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorController extends AController {

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
		$this::$_menu->insertMenuItem(200, null, "Benutzer", "/actors");
        $this::$_menu->insertMenuItem(210, 200, "Benutzer erstellen", "/actors/create");
	}

    /**
     * @inheritDoc
     */
    public function getRoutes(): array {
        return array(
            "/actors" => array("controller" => __CLASS__, "method" => "index"),
            "/actors/search" => array("controller" => __CLASS__, "method" => "search"),
            "/actors/{actor}" => array("controller" => __CLASS__, "method" => "update"),
            "/actors/create" => array("controller" => __CLASS__, "method" => "create"),
            "/actors/delete/{actor}" => array("controller" => __CLASS__, "method" => "delete"),
            "/actors/roles/{actor}" => array("controller" => __CLASS__, "method" => "roles")
        );
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     * @throws JsonException
     * @throws SystemException
     */
	public function index(): AResponse {
        $response = new ResponseHTML();
        $params = RequestHelper::getPaginationParams();

        $cache = Core::$_response_cache;
        $cache->initCacheFor(__METHOD__, ...$params);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."actor/index.html");
        if( $cache->isUpToDate() ) {
            $view_content = $cache->getContent();
        } else {
            $pagination = "";
            HTMLHelper::getPagination( $params['page'],  Actor::getNumActors(), $params['limit'], $pagination);

            $view = new Template(PATH_VIEWS."actor/index.html");
            $view->set("result_list", Actor::find(array(), $params['order'], $params['direction'], $params['limit'], $params['page']));
            $view->set("pagination", $pagination);
            $view_content = $view->parse();
            $cache->saveContent($view_content);
        }

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $this::$_menu);
		$template->set("view", $view_content);

        $response->setOutput($template->parse());
		return $response;
	}

    /**
     *
     * @return AResponse
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function search(): AResponse {
        $response = new ResponseHTML();
        $search_string = Core::$_request->get("search_string");

        $params = array($search_string);
        $cache = Core::$_response_cache;
        $cache->initCacheFor(__METHOD__, ...$params);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."actor/search.html");

        if( $cache->isUpToDate() ) {
            $view_content = $cache->getContent();
        } else {
            $results = array();
            if( $search_string !== null ) {
                $pdo = Core::$_connection_manager->getConnection("mvc");
                $sql = "SELECT * FROM actors WHERE first_name LIKE :word OR last_name LIKE :word OR email LIKE :word";
                $pdo->prepare($sql);
                $pdo->bindParam("word", "%".$search_string."%");
                $pdo->setFetchMode(PDO::FETCH_CLASS, Actor::class );
                $results = $pdo->execute()->fetchAll();
            }
            $view = new Template(PATH_VIEWS."actor/search.html");
            $view->set("search_string", $search_string);
            $view->set("result_list", $results);
            $view_content = $view->parse();
            $cache->saveContent($view_content);
        }

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $this::$_menu);
        $template->set("view", $view_content);
        $response->setOutput($template->parse());
        return $response;
    }

    /**
     * @throws Exception
     */
    public function create(): AResponse {
		if( !$this::$_actor_role->canCreateAll() ) {
			redirect("/error/403");
		}
		if( isset($_POST['create']) ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$actor = new Actor();
				$actor->email = $_POST["email"];
				$actor->password = $_POST["password"];
				$actor->first_name = $_POST["first_name"];
				$actor->last_name = $_POST["last_name"];
				$actor->create();
				$this->savePermissions($actor);
				redirect("/actors");
			}
		}

        $response = new ResponseHTML();

        $cache = Core::$_response_cache;
        $cache->initCacheFor(__METHOD__);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."actor/create.html");
        if( $cache->isUpToDate() ) {
            $view_content = $cache->getContent();
        } else {
            $view = new Template(PATH_VIEWS."actor/create.html");
            $view->set("role_options", ActorRole::find());
            $view->set("access_permissions", array());

            $view_content = $view->parse();
            $cache->saveContent($view_content);
        }

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $this::$_menu);
        $template->set("view", $view_content);

        $response->setOutput($template->parse());
		return $response;
	}


    /**
     * @throws SystemException
     * @throws JsonException
     */
    public function update( Actor $actor ): AResponse {
		if( !$this::$_actor_role->canUpdate($actor->id) ) {
			redirect("/error/403");
		}
		if( isset($_POST['cancel']) ) {
			redirect("/actors");
		}
		if( isset($_POST['update']) ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$actor->email = $_POST["email"];
				$actor->password = $_POST["password"];
				$actor->first_name = $_POST["first_name"];
				$actor->last_name = $_POST["last_name"];
                $actor->login_fails = (int) $_POST["login_fails"];
                $actor->login_disabled = (int) $_POST["login_disabled"];
				$actor->update();
                redirect("/actors");
			}
		}

		$response = new ResponseHTML();

        $access_permissions = array();
        $view = new Template(PATH_VIEWS."actor/edit.html");
        $view->set("actor", $actor);
        $view->set("role_options", ActorRole::find());
        $view->set("access_permissions", $access_permissions);
        $view_content = $view->parse();

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", $this::$_menu);
        $template->set("view", $view_content);

        $response->setOutput($template->parse());
        return $response;
	}

    /**
     * @param Actor $actor
     *
     * @return AResponse
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function delete( Actor $actor ): AResponse {
        if( !$this::$_actor_role->canDelete($actor->id) ) {
            redirect("/error/403");
        }
        if( isset($_POST['cancel']) ) {
            redirect("/actors");
        }
        $delete_date = new DateTime();
        $actor->deleted = $delete_date->format('Y-m-d H:i:s');
        $actor->update();
        redirect("/actors");
        return new ResponseHTML();
    }

    /**
     * @throws SystemException
     * @throws Exception
     */
    public function roles( Actor $actor ): AResponse {
		if( !$this::$_actor_role->canUpdate($actor->id) ) {
			redirect("/error/403");
		}
		if( isset($_POST['cancel']) ) {
			redirect("/actors");
		}

		if( isset($_POST['update']) ) {
			$this->savePermissions($actor);
			redirect("/actors");
		}

		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");

		$template->set("navigation", $this::$_menu);
		$template->set("actor", $actor);
	    $template->set("role_options", ActorRole::find());
	    $template->set("access_permissions", AccessPermission::find(array(
		    ["actor_id", "=", $actor->id]
	    )));
	    $template->set("view", new Template(PATH_VIEWS."actor/roles.html"));
		$response->setOutput($template->parse());
		return $response;
	}

    /**
     * Checks if all required values are set
     *
     * @return bool
     */
	private function postIsValid(): bool {
		if( !isset($_POST["email"]) || $_POST["email"] === "" ) {
			return false;
		}
		if( !isset($_POST["password"]) || ( isset($_POST["create"]) && $_POST["password"] === '' ) ) {
            return false;
		}
		if( !array_key_exists("password", $_POST) || !array_key_exists("password_verify", $_POST) || $_POST["password"] !== $_POST["password_verify"] ) {
            return false;
		}
		if( !isset($_POST["first_name"]) || $_POST["first_name"] === "" ) {
            return false;
		}
		if( !isset($_POST["last_name"]) || $_POST["last_name"] === "" ) {
            return false;
		}
		return true;
	}

    /**
     * Save the permissions for the given actor
     *
     * @param Actor $actor
     * @return void
     *
     * @throws JsonException
     * @throws SystemException
     */
	private function savePermissions( Actor $actor ): void {
		if( $actor->id === 0 ) {
			return;
		}
		$roles = array();
		foreach( $_POST['role'] as $domain => $entry_domain ) {
			if( (int)$entry_domain["role"] > 0 ) {
				$roles[$domain][null][null] = $entry_domain["role"];
			}
			foreach( $entry_domain["controller"] as $controller => $entry_controller ) {
				$controller = str_replace("-", "\\", $controller);
				if( (int)$entry_controller["role"] > 0 ) {
					$roles[$domain][$controller][null] = $entry_controller["role"];
				}
				foreach( $entry_controller["method"] as $method => $role ) {
					if( (int)$role > 0 ) {
						$roles[$domain][$controller][$method] = $role;
					}
				}
			}
		}
        $actor->deletePermissions();
		foreach( $roles as $domain => $controllers ) {
			foreach( $controllers as $controller => $methods ) {
				foreach( $methods as $method => $role ) {
					$actor_permission = new AccessPermission();
					$actor_permission->actor_id = $actor->id;
					$actor_permission->role_id = $role;
					$actor_permission->domain = $domain;
					$actor_permission->controller = ( $controller !== '' ) ? $controller : null;
					$actor_permission->method = ( $method !== '' ) ? $method : null;
					$actor_permission->create();
				}
			}
		}
	}

	public function __toString(): string {
		return __CLASS__;
	}
}
