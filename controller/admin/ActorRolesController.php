<?php

namespace controller\admin;

use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Configuration;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\RequestMethod;
use lib\core\enums\SystemMessageType;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\core\tree\RoleTree;
use models\ActorRoleModel;
use repositories\ActorRoleRepository;

/**
 * @see \lib\core\blueprints\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("actor-roles")]
class ActorRolesController extends AController {

	private readonly ActorRoleRepository $role_repository;

	/**
	 * @param Configuration $config
	 * @throws SystemException
	 */
	public function __construct(ActorRoleRepository $role_repository, Configuration $config) {
		parent::__construct($config);
		$this->role_repository = $role_repository;
	}

	/**
	 * Get a list of all actor roles
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("", RequestMethod::GET)]
	public function index(): AResponse {
		$view = new Template(PATH_VIEWS . "actor_roles/index.html");
		$actor_roles_tree = App::getInstanceOf(RoleTree::class);
		TemplateData::set("role_tree", $actor_roles_tree);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->render());

		return new HtmlResponse($template->render());
	}

	/**
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("create", RequestMethod::GET)]
	public function create(bool $cache_refresh = false): AResponse {
		$view = new Template(PATH_VIEWS . "actor_roles/create.html");
		TemplateData::set("option_list", $this->role_repository->getAll());

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->render());

		$content = $template->render();
		return new HtmlResponse($content);
	}

	/**
	 * @return AResponse
	 * @throws SystemException
	 */
	#[Route("create", RequestMethod::POST)]
	public function createSubmit(): AResponse {
		if( !App::$curr_actor_role->canCreateAll() ) {
			redirect("/error/403");
		}

		$is_valid = $this->postIsValid();
		if( $is_valid ) {
			$role = App::getInstanceOf(ActorRoleModel::class);
			$this->setRoleParams($role);
			$this->role_repository->createObject($role);
			TemplateData::setSystemMessage("Der Zugriffstype wurde erfolgreich erstellt.");
		} else {
			TemplateData::setSystemMessage("Es ist ein Fehler aufgetreten.", SystemMessageType::ERROR);
		}

		return $this->create();
	}

	/**
	 * @param ActorRoleModel $role
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("/{role_id}", RequestMethod::GET)]
	public function update(ActorRoleModel $role): AResponse {
		if( !App::$curr_actor_role->canUpdateAll() ) {
			redirect("/error/403");
		}

		$view = new Template(PATH_VIEWS . "actor_roles/edit.html");
		TemplateData::set("role", $role);
		TemplateData::set("actor_role", App::$curr_actor_role);
		TemplateData::set("option_list", $this->role_repository->find(array(["id", "!=", $role->id])));

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->render());

		$content = $template->render();
		return new HTMLResponse($content);
	}

	/**
	 * @param ActorRoleModel $role
	 * @return AResponse
	 * @throws SystemException
	 */
	#[Route("/{role_id}", RequestMethod::PUT)]
	public function updateSubmit(ActorRoleModel $role): AResponse {
		if( !App::$curr_actor_role->canUpdateAll() ) {
			redirect("/error/403");
		}

		$is_valid = $this->postIsValid();
		if( $is_valid ) {
			$this->setRoleParams($role);
			$this->role_repository->updateObject($role);
			TemplateData::setSystemMessage("Die Benutzerrolle wurde erfolgreich aktualisiert.");
		} else {
			TemplateData::setSystemMessage("Es ist ein Fehler aufgetreten.", SystemMessageType::ERROR);
		}
		return $this->update($role, true);
	}

	/**
	 * @param ActorRoleModel $role
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("delete/{role_id}", RequestMethod::DELETE)]
	public function deleteSubmit(ActorRoleModel $role): AResponse {
		if( !App::$curr_actor_role->canDeleteAll() ) {
			redirect("/error/403");
		}

		$this->role_repository->deleteObject($role);
		App::setAsSingleton(RoleTree::class, RoleTree::getInstance(true));
		TemplateData::setSystemMessage("Die Benutzerrolle wurde erfolgreich gelöscht.");
		return $this->index();
	}

	/**
	 * @param array $settings
	 * @return int
	 */
	private function getPermissions(array $settings): int {
		$permissions = 0b0000;
		if( isset($settings["read"]) ) {
			$permissions = ActorRoleModel::$CAN_READ;
		}
		if( isset($settings["create"]) ) {
			$permissions |= ActorRoleModel::$CAN_CREATE;
		}
		if( isset($settings["update"]) ) {
			$permissions |= ActorRoleModel::$CAN_UPDATE;
		}
		if( isset($settings["delete"]) ) {
			$permissions |= ActorRoleModel::$CAN_DELETE;
		}
		return $permissions;
	}

	/**
	 * @return bool
	 */
	private function postIsValid(): bool {
		return (App::$request->contains("name") && App::$request->get("name") !== "");
	}

	/**
	 * Sets the actor role parameters of the given role
	 *
	 * @param ActorRoleModel $role
	 *
	 * @return void
	 */
	private function setRoleParams(ActorRoleModel $role): void {
		$role->name = App::$request->get("name");
		$role->child_of = (App::$request->contains("child_of") && (int)App::$request->get("child_of") > 0) ? (int)App::$request->get("child_of") : null;
		$role->rights_all = (App::$request->contains("all")) ? $this->getPermissions(App::$request->get("all")) : 0b000;
		$role->rights_group = (App::$request->contains("group")) ? $this->getPermissions(App::$request->get("group")) : 0b000;
		$role->rights_own = (App::$request->contains("own")) ? $this->getPermissions(App::$request->get("own")) : 0b000;
	}
}
