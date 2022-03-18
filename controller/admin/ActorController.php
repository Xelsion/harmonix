<?php

namespace controller\admin;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\Router;
use system\classes\Template;
use system\classes\responses\ResponseHTML;
use models\Actor;
use models\ActorRole;
use models\ActorPermission;

/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorController extends AController {

	/**
	 * @param Router $router
	 * @see \system\interfaces\IController
	 */
	public function init( Router $router ): void {
		// Add routes to router
		$router->addRoute("/actors", __CLASS__);
		$router->addRoute("/actors/{actor}", __CLASS__, "update");
		$router->addRoute("/actors/create", __CLASS__, "create");
		$router->addRoute("/actors/roles/{actor}", __CLASS__, "roles");

		// Add MenuItems to the Menu
		$this::$_menu->insertMenuItem(200, null, "Benutzer", "/actors");
        $this::$_menu->insertMenuItem(210, 200, "Benutzer erstellen", "/actors/create");
	}

	/**
	 * @return AResponse
	 * @see \system\interfaces\IController
	 */
	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");

		$results = Actor::findAll();

		$template->set("navigation", $this::$_menu);
		$template->set("result_list", $results);
		$template->set("view", new Template(PATH_VIEWS."actor/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

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

		$role_options = ActorRole::findAll();
		$actor_permissions = array();
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", $this::$_menu);
		$template->set("role_options", $role_options);
		$template->set("actor_permissions", $actor_permissions);
		$template->set("view", new Template(PATH_VIEWS."actor/create.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function update( Actor $actor ): AResponse {
		if( !$this::$_actor_role->canUpdateAll() ) {
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
		$template->set("navigation", $this::$_menu);
		$template->set("view", new Template(PATH_VIEWS."actor/edit.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function roles( Actor $actor ): AResponse {
		if( !$this::$_actor_role->canUpdateGroup() ) {
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

		$role_options = ActorRole::findAll();
		$actor_permissions = ActorPermission::find(array(
			array(
				"actor_id",
				"=",
				$actor->id
			)
		));

		$template->set("navigation", $this::$_menu);
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

	private function savePermissions( Actor $actor ): void {
		if( $actor->id === 0 ) {
			return;
		}
		$roles = array();
		foreach( $_POST['role'] as $path => $entry_path ) {
			if( (int)$entry_path["role"] > 0 ) {
				$roles[$path][null][null] = $entry_path["role"];
			}
			foreach( $entry_path["controller"] as $controller => $entry_controller ) {
				$controller = str_replace("-", "\\", $controller);
				if( (int)$entry_controller["role"] > 0 ) {
					$roles[$path][$controller][null] = $entry_controller["role"];
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

	public function __toString(): string {
		return __CLASS__;
	}
}