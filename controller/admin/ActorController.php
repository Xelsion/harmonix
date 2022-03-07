<?php

namespace controller\admin;

use core\abstracts\AController;
use core\abstracts\AResponse;
use core\classes\responses\ResponseHTML;
use core\classes\Router;
use core\classes\Template;
use core\Core;
use models\Actor;
use models\ActorRole;
use models\ActorPermission;

class ActorController extends AController {

	public function init( Router $router ): void {
		// Add routes to router
		$router->addRoute("/actors", __CLASS__);
		$router->addRoute("/actors/{actor}", __CLASS__, "update");
		$router->addRoute("/actors/create", __CLASS__, "create");
		$router->addRoute("/actors/roles/{actor}", __CLASS__, "roles");

		// Add MenuItems to the Menu
		static::$_menu->insertMenuItem(200, null, "Benutzer", "/actors");
		static::$_menu->insertMenuItem(210, 200, "Benutzer erstellen", "/actors/create");
	}

	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");

		$results = Actor::findAll();

		$template->set("navigation", static::$_menu);
		$template->set("result_list", $results);
		$template->set("view", new Template(PATH_VIEWS."actor/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function create(): AResponse {
		if( !Core::$_actor_role->canCreateAll() ) {
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
				redirect("/actors");
			}
		}
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", static::$_menu);
		$template->set("view", new Template(PATH_VIEWS."actor/create.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function update( Actor $actor ): AResponse {
		if( !Core::$_actor_role->canUpdateAll() ) {
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
				$actor->update();
				redirect("/actors");
			}
		}
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("actor", $actor);
		$template->set("navigation", static::$_menu);
		$template->set("view", new Template(PATH_VIEWS."actor/edit.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function roles( Actor $actor ): AResponse {
		if( !Core::$_actor_role->canUpdateAll() ) {
			redirect("/error/403");
		}

		if( isset($_POST['cancel']) ) {
			redirect("/actors");
		}
		if( isset($_POST['update']) ) {
			$roles = array();
			foreach( $_POST['role'] as $path => $entry_path ) {
				if( (int)$entry_path["role"] > 0 ) {
					$roles[$path][""][""] = $entry_path["role"];
				}
				foreach( $entry_path["controller"] as $controller => $entry_controller ) {
					$controller = str_replace("-", "\\", $controller);
					if( (int)$entry_controller["role"] > 0 ) {
						$roles[$path][$controller][""] = $entry_controller["role"];
					}
					foreach( $entry_controller["method"] as $method => $role ) {
						if( (int)$role > 0 ) {
							$roles[$path][$controller][$method] = $role;
						}
					}
				}
			}
			$actor_permission = new ActorPermission();
			$actor_permission->actor_id = $actor->id;
			$actor_permission->delete();
			foreach( $roles as $path => $controllers ) {
				foreach( $controllers as $controller => $methods ) {
					foreach( $methods as $method => $role ) {
						$actor_permission = new ActorPermission();
						$actor_permission->actor_id = $actor->id;
						$actor_permission->role_id = $role;
						$actor_permission->path = $path;
						$actor_permission->controller = ( $controller !== '' ) ? $controller : null;
						$actor_permission->method = ( $method !== '' ) ? $method : null;
						$actor_permission->create();
					}
				}
			}
		}

		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");

		$role_options = ActorRole::findAll();
		$actor_permissions = ActorPermission::find(array(
			array(
				"actor_id",
				"=",
				$actor->id
			)
		));

		$template->set("navigation", static::$_menu);
		$template->set("actor", $actor);
		$template->set("role_options", $role_options);
		$template->set("actor_permissions", $actor_permissions);
		$template->set("view", new Template(PATH_VIEWS."actor/roles.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	private function postIsValid(): bool {
		$is_valid = true;
		if( !isset($_POST["email"]) || $_POST["email"] === "" ) {
			$is_valid = false;
		}
		if( !isset($_POST["password"]) || ( isset($_POST["create"]) && $_POST["password"] === '' ) ) {
			$is_valid = false;
		}
		if( !array_key_exists("password", $_POST) || !array_key_exists("password_verify", $_POST) || $_POST["password"] !== $_POST["password_verify"] ) {
			$is_valid = false;
		}
		if( !isset($_POST["first_name"]) || $_POST["first_name"] === "" ) {
			$is_valid = false;
		}
		if( !isset($_POST["last_name"]) || $_POST["last_name"] === "" ) {
			$is_valid = false;
		}
		return $is_valid;
	}

	public function __toString(): string {
		return __CLASS__;
	}
}