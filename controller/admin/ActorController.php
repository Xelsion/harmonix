<?php

namespace controller\admin;

use core\abstracts\AController;
use core\abstracts\AResponse;
use core\classes\responses\ResponseHTML;
use core\classes\Router;
use core\classes\Template;
use models\Actor;

class ActorController extends AController {

	public function init( Router $router ): void {
		// Add routes to router
		$router->addRoute("/actors", __CLASS__);
		$router->addRoute("/actors/create", __CLASS__, "create");
		$router->addRoute("/actors/update/{actor}", __CLASS__, "update");
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
		if( isset($_POST['create']) ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$actor = new Actor();
				$actor->email = $_POST["email"];
				$actor->password = $_POST["password"];
				$actor->first_name = $_POST["first_name"];
				$actor->last_name = $_POST["last_name"];
				$actor->create();
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
		if( isset($_POST['update']) ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$actor->email = $_POST["email"];
				$actor->password = $_POST["password"];
				$actor->first_name = $_POST["first_name"];
				$actor->last_name = $_POST["last_name"];
				$actor->update();
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