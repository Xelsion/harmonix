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
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use lib\helper\HtmlHelper;
use lib\helper\RequestHelper;
use lib\helper\StringHelper;
use models\StoredObjectModel;
use repositories\ActionStorageRepository;
use repositories\ActorRepository;

#[Route("/action-storage")]
class ActionStorageController extends AController {

	private readonly ActionStorageRepository $repository;

	/**
	 * @param ActionStorageRepository $repository
	 * @param Configuration $config
	 */
	public function __construct(ActionStorageRepository $repository, Configuration $config) {
		parent::__construct($config);
		$this->repository = $repository;
	}

	/**
	 * @throws SystemException
	 */
	#[Route("/", RequestMethod::GET, RequestMethod::POST)]
	public function index(): AResponse {
		$conditions = [];
		$filter = App::getInstanceOf(RequestHelper::class)->getFilter(["actor" => "actor_id"]);
		if( !StringHelper::isNullOrEmpty($filter["date_from"]) ) {
			$conditions[] = ["created", ">=", $filter["date_from"] . " 00:00:00"];
		}
		if( !StringHelper::isNullOrEmpty($filter["date_to"]) ) {
			$conditions[] = ["created", "<=", $filter["date_to"] . " 23:59:59"];
		}
		if( !StringHelper::isNullOrEmpty($filter["actor_id"]) ) {
			$conditions[] = ["actor_id", "=", $filter["actor_id"]];
		}
		if( !StringHelper::isNullOrEmpty($filter["action"]) ) {
			$conditions[] = ["action", "=", $filter["action"]];
		}

		$params = App::getInstanceOf(RequestHelper::class)->getPaginationParams();
		$pagination = "";
		if( !empty($conditions) ) {
			$results = $this->repository->find($conditions);
			HTMLHelper::getPagination($params['page'], count($results), $params['limit'], $pagination);
		} else {
			HTMLHelper::getPagination($params['page'], $this->repository->getNumRows(), $params['limit'], $pagination);
		}

		TemplateData::set("filter", App::$request->get("filter"));
		TemplateData::set("pagination", $pagination);
		TemplateData::set("stored_objects", $this->repository->find($conditions, "id", "desc", $params['limit'], $params['page']));
		TemplateData::set("actor_list", App::getInstanceOf(ActorRepository::class)->find());

		$view = new Template(PATH_VIEWS . "action_storage/index.html");
		TemplateData::set("view", $view->render());

		$template = new Template(PATH_VIEWS . "template.html");
		return new HtmlResponse($template->render());
	}

	/**
	 * @param StoredObjectModel $obj
	 * @return AResponse
	 * @throws SystemException
	 */
	#[Route("/undo/{id}", RequestMethod::POST)]
	public function undoAction(StoredObjectModel $obj): AResponse {
		$this->repository->undoAction($obj);
		TemplateData::setSystemMessage("Die Aktion wurde Rückgängig gemacht.");
		return $this->index();
	}

}