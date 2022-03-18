<?php

namespace controller\admin;

use system\abstracts\AResponse;
use system\abstracts\AController;
use system\classes\responses\ResponseHTML;
use system\classes\Router;
use system\classes\Template;
use models\ActorRole;

/**
 * @see \system\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorRolesController extends AController {

	/**
	 * @param Router $router
	 * @see \system\interfaces\IController
	 */
	public function init( Router $router ): void {
		// Add routes to router
		$router->addRoute("/actor-roles", __CLASS__);
		$router->addRoute("/actor-roles/{role}", __CLASS__, "update");
		$router->addRoute("/actor-roles/create", __CLASS__, "create");

		// Add MenuItems to the Menu
		$this::$_menu->insertMenuItem(300, null, "Rollen", "/actor-roles");
        $this::$_menu->insertMenuItem(310, 300, "Rolle erstellen", "/actor-roles/create");
	}

	/**
	 * @see \system\interfaces\IController
	 * @return AResponse
	 */
	public function index(): AResponse {
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");

		$results = ActorRole::findAll();

		$template->set("navigation", $this::$_menu);
		$template->set("result_list", $results);
		$template->set("view", new Template(PATH_VIEWS."actor_roles/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function create(): AResponse {
		if( isset($_POST['create']) ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$all = ( isset($_POST["all"]) ) ? $this->getPermissions($_POST["all"]) : 0b000;
				$group = ( isset($_POST["group"]) ) ? $this->getPermissions($_POST["group"]) : 0b000;
				$own = ( isset($_POST["own"]) ) ? $this->getPermissions($_POST["own"]) : 0b000;
				$role = new ActorRole();
				$role->name = $_POST["name"];
				$role->child_of = ( (int)$_POST["child_of"] > 0 ) ? (int)$_POST["child_of"] : null;
				$role->rights_all = $all;
				$role->rights_group = $group;
				$role->rights_own = $own;
				$role->create();
				redirect("/actor-roles");
			}
		}
		$results = ActorRole::findAll();

		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", $this::$_menu);
		$template->set("option_list", $results);
		$template->set("view", new Template(PATH_VIEWS."actor_roles/create.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	public function update( ActorRole $role ): AResponse {
		if( isset($_POST['cancel']) ) {
			redirect("/actor-roles");
		}
		if( isset($_POST['update']) ) {

			$is_valid = $this->postIsValid();
			if( $is_valid ) {

				$all = ( isset($_POST["all"]) ) ? $this->getPermissions($_POST["all"]) : 0b000;
				$group = ( isset($_POST["group"]) ) ? $this->getPermissions($_POST["group"]) : 0b000;
				$own = ( isset($_POST["own"]) ) ? $this->getPermissions($_POST["own"]) : 0b000;
				$role->name = $_POST["name"];
				$role->child_of = ( (int)$_POST["child_of"] > 0 ) ? (int)$_POST["child_of"] : null;
				$role->rights_all = $all;
				$role->rights_group = $group;
				$role->rights_own = $own;
				$role->update();
				redirect("/actor-roles");
			}
		}
		$results = ActorRole::find(array(
			array(
				"id",
				"!=",
				$role->id
			)
		));
		$response = new ResponseHTML();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("role", $role);
		$template->set("option_list", $results);
		$template->set("navigation", $this::$_menu);
		$template->set("view", new Template(PATH_VIEWS."actor_roles/edit.html"));
		$response->setOutput($template->parse());
		return $response;
	}

	private function getPermissions( array $settings ): int {
		$permissions = 0b0000;
		if( isset($settings["read"]) ) {
			$permissions = ActorRole::$CAN_READ;
		}
		if( isset($settings["create"]) ) {
			$permissions |= ActorRole::$CAN_CREATE;
		}
		if( isset($settings["update"]) ) {
			$permissions |= ActorRole::$CAN_UPDATE;
		}
		if( isset($settings["delete"]) ) {
			$permissions |= ActorRole::$CAN_DELETE;
		}
		return $permissions;
	}

	private function postIsValid(): bool {
		$is_valid = true;
		if( !isset($_POST["name"]) || $_POST["name"] === "" ) {
			$is_valid = false;
		}
		return $is_valid;
	}
}