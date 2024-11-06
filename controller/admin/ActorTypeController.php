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
use models\ActorTypeModel;
use repositories\ActorTypeRepository;

#[Route("actor-types")]
class ActorTypeController extends AController {


	private readonly ActorTypeRepository $type_repository;

	/**
	 * @param ActorTypeRepository $type_repository
	 * @param Configuration $config
	 */
	public function __construct(ActorTypeRepository $type_repository, Configuration $config) {
		parent::__construct($config);
		$this->type_repository = $type_repository;
	}

	/**
	 * Get a list of all actor types
	 *
	 * @return \lib\core\blueprints\AResponse
	 *
	 * @throws SystemException
	 * @throws JsonException
	 */
	#[Route("/", RequestMethod::GET)]
	public function index(): AResponse {
		$view = new Template(PATH_VIEWS . "actor_types/index.html");
		TemplateData::set('result_list', $this->type_repository->getAll());

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 * @return \lib\core\blueprints\AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("create", RequestMethod::GET)]
	public function create(): AResponse {
		if( !App::$curr_actor_role->canCreateAll() ) {
			redirect("/error/403");
		}
		$view = new Template(PATH_VIEWS . "actor_types/create.html");

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
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
			$actor_type = App::getInstanceOf(ActorTypeModel::class);
			$actor_type->name = App::$request->get("name");
			$this->type_repository->createObject($actor_type);
			TemplateData::setSystemMessage("Die Benutzertype wurde erfolgreich erstellt.");
		} else {
			TemplateData::setSystemMessage("Es ist ein Fehler aufgetreten.", SystemMessageType::ERROR);
		}
		return $this->create();
	}


	/**
	 *
	 * @param ActorTypeModel $actor_type
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("{actor_type_id}", RequestMethod::GET)]
	public function update(ActorTypeModel $actor_type): AResponse {
		if( !App::$curr_actor_role->canUpdate($actor_type->id) ) {
			redirect("/error/403");
		}

		$view = new Template(PATH_VIEWS . "actor_types/edit.html");
		TemplateData::set("actor_type", $actor_type);
		TemplateData::set("actor_role", App::$curr_actor_role);

		$template = new Template(PATH_VIEWS . "template.html");
		TemplateData::set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

	/**
	 *
	 * @param ActorTypeModel $actor_type
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("{actor_type_id}", RequestMethod::PUT)]
	public function updateSubmit(ActorTypeModel $actor_type): AResponse {
		if( !App::$curr_actor_role->canUpdate($actor_type->id) ) {
			redirect("/error/403");
		}

		$is_valid = $this->postIsValid();
		if( $is_valid ) {
			$actor_type->name = App::$request->get('name');
			$this->type_repository->updateObject($actor_type);
			TemplateData::setSystemMessage("Die Benutzertype wurde erfolgreich aktualisiert.");
		} else {
			TemplateData::setSystemMessage("Es ist ein Fehler aufgetreten.", SystemMessageType::ERROR);
		}
		return $this->update($actor_type);
	}

	/**
	 * @param ActorTypeModel $actor_type
	 *
	 * @return AResponse
	 *
	 * @throws SystemException
	 */
	#[Route("delete/{actor_type_id}", RequestMethod::DELETE)]
	public function deleteSubmit(ActorTypeModel $actor_type): AResponse {
		if( !App::$curr_actor_role->canDelete($actor_type->id) ) {
			redirect("/error/403");
		}
		$this->type_repository->deleteObject($actor_type);
		TemplateData::setSystemMessage("Die Benutzertype wurde erfolgreich gelöscht.");
		return $this->index();
	}

	/**
	 * Checks if all required values are setClass
	 *
	 * @return bool
	 */
	private function postIsValid(): bool {
		return !(!App::$request->contains('name') || App::$request->get('name') === "");
	}
}
