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
use lib\core\Router;
use models\AccessRestrictionModel;
use models\AccessRestrictionTypeModel;
use repositories\AccessRestrictionRepository;
use repositories\AccessRestrictionTypeRepository;
use repositories\ActorRoleRepository;

#[Route("restrictions")]
class RestrictionController extends AController {

	private readonly AccessRestrictionRepository $restriction_repository;

	private readonly AccessRestrictionTypeRepository $restriction_type_repository;

	private readonly ActorRoleRepository $role_repository;

	public function __construct(AccessRestrictionRepository $restriction_repository, AccessRestrictionTypeRepository $restriction_type_repository, ActorRoleRepository $role_repository, Configuration $config) {
		parent::__construct($config);
		$this->restriction_repository = $restriction_repository;
		$this->restriction_type_repository = $restriction_type_repository;
		$this->role_repository = $role_repository;
	}

	/**
	 * Get a list of all restrictions
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("", RequestMethod::GET)]
	public function index(): AResponse {
		$access_restrictions = $this->restriction_repository->getAll();
		$current_restrictions = array();
		foreach( $access_restrictions as $restriction ) {
			$current_restrictions[$restriction->domain][$restriction->controller][$restriction->method] = array(
				"role" => $restriction->role_id,
				"type" => $restriction->restriction_type
			);
		}

		$view = new Template(PATH_VIEWS . "restrictions/index.html");
		TemplateData::set("routes", App::getInstanceOf(Router::class)->getSortedRoutes());
		TemplateData::set("current_restrictions", $current_restrictions);
		TemplateData::set("role_options", $this->role_repository->getAll());
		TemplateData::set("type_options", $this->restriction_type_repository->getAll());

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 * Get a list of all restrictions
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("", RequestMethod::PUT)]
	public function updateSubmit(): AResponse {
		if( !App::$curr_actor_role->canCreateAll() ) {
			redirect("/error/403");
		}

		$this->saveRestrictions();
		TemplateData::setSystemMessage("Die Zugriffsrechte wurden erfolgreich aktualisiert.");
		return $this->index();
	}

	/**
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("types", RequestMethod::GET)]
	public function types(): AResponse {
		$view = new Template(PATH_VIEWS . "restrictions/types.html");
		TemplateData::set("type_list", $this->restriction_type_repository->getAll());

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("types/create", RequestMethod::GET)]
	public function typesCreate(): AResponse {
		if( !App::$curr_actor_role->canCreateAll() ) {
			redirect("/error/403");
		}

		$view = new Template(PATH_VIEWS . "restrictions/types_create.html");

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("types/create", RequestMethod::POST)]
	public function typesCreateSubmit(): AResponse {
		if( !App::$curr_actor_role->canCreateAll() ) {
			redirect("/error/403");
		}

		$is_valid = $this->postIsValid();
		if( $is_valid ) {
			$type = new AccessRestrictionTypeModel();
			$type->name = App::$request->get("name");
			$type->include_siblings = (App::$request->get("include_siblings")) ? 1 : 0;
			$type->include_children = (App::$request->get("include_children")) ? 1 : 0;
			$type->include_descendants = (App::$request->get("include_descendants")) ? 1 : 0;
			$this->restriction_type_repository->createObject($type);
			TemplateData::setSystemMessage("Der Zugriffstype wurde erfolgreich erstellt.");
		} else {
			TemplateData::setSystemMessage("Es ist ein Fehler aufgetreten.", SystemMessageType::ERROR);
		}
		return $this->typesCreate();
	}

	/**
	 * @param AccessRestrictionTypeModel $type
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("types/{type_id}", RequestMethod::GET)]
	public function typesUpdate(AccessRestrictionTypeModel $type): AResponse {
		if( !App::$curr_actor_role->canUpdateAll() ) {
			redirect("/error/403");
		}

		$view = new Template(PATH_VIEWS . "restrictions/types_edit.html");
		TemplateData::set("type", $type);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 * @param AccessRestrictionTypeModel $type
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("types/{type_id}", RequestMethod::PUT)]
	public function typesUpdateSubmit(AccessRestrictionTypeModel $type): AResponse {
		if( !App::$curr_actor_role->canUpdateAll() ) {
			redirect("/error/403");
		}

		$is_valid = $this->postIsValid();
		if( $is_valid ) {
			$type->name = App::$request->get("name");
			$type->include_siblings = (App::$request->get("include_siblings")) ? 1 : 0;
			$type->include_children = (App::$request->get("include_children")) ? 1 : 0;
			$type->include_descendants = (App::$request->get("include_descendants")) ? 1 : 0;
			$this->restriction_type_repository->updateObject($type);
			TemplateData::setSystemMessage("Der Zugriffstype wurde erfolgreich aktualisiert.");
		} else {
			TemplateData::setSystemMessage("Es ist ein Fehler aufgetreten.", SystemMessageType::ERROR);
		}
		return $this->typesUpdate($type);
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
				$restrictions[$domain][null][null] = array($entry_domain["role"], $entry_domain["type"]);
			}
			foreach( $entry_domain["controller"] as $controller => $entry_controller ) {
				$controller = str_replace("-", "\\", $controller);
				if( (int)$entry_controller["role"] > 0 && (int)$entry_controller["type"] > 0 ) {
					$restrictions[$domain][$controller][null] = array(
						$entry_controller["role"],
						$entry_controller["type"]
					);
				}
				foreach( $entry_controller["method"] as $method => $entry_method ) {
					if( (int)$entry_method["role"] > 0 && (int)$entry_method["type"] > 0 ) {
						$restrictions[$domain][$controller][$method] = array(
							$entry_method["role"],
							$entry_method["type"]
						);
					}
				}
			}
		}

		$this->restriction_repository->deleteAll();
		foreach( $restrictions as $domain => $controllers ) {
			foreach( $controllers as $controller => $methods ) {
				foreach( $methods as $method => $entry ) {
					$restriction = new AccessRestrictionModel();
					$restriction->domain = $domain;
					$restriction->controller = ($controller !== "") ? $controller : null;
					$restriction->method = ($method !== "") ? $method : null;
					$restriction->role_id = $entry[0];
					$restriction->restriction_type = $entry[1];
					$this->restriction_repository->createObject($restriction);
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
		if( !App::$request->contains("name") || App::$request->get("name") === "" ) {
			$is_valid = false;
		}
		return $is_valid;
	}

}
