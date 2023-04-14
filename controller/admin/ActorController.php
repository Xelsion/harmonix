<?php
namespace controller\admin;

use DateTime;
use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Configuration;
use lib\core\classes\Template;
use lib\core\exceptions\SystemException;
use lib\core\Request;
use lib\core\response_types\HtmlResponse;
use lib\core\Router;
use lib\helper\HtmlHelper;
use lib\helper\RequestHelper;
use models\ActorModel;
use models\entities\AccessPermission;
use models\entities\Actor;
use repositories\AccessPermissionRepository;
use repositories\ActorRepository;
use repositories\ActorRoleRepository;
use repositories\ActorTypeRepository;


/**
 * @see \lib\core\blueprints\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("actors")]
class ActorController extends AController {

    private readonly ActorRepository $actor_repository;
    private readonly ActorRoleRepository $role_repository;
    private readonly ActorTypeRepository $type_repository;
    private readonly AccessPermissionRepository $permission_repository;

    private readonly Request $request;

    public function __construct(ActorRepository            $actor_repository,
                                ActorRoleRepository        $role_repository,
                                ActorTypeRepository        $type_repository,
                                AccessPermissionRepository $permission_repository,
                                Request                    $request,
                                Configuration              $config
    ) {
        parent::__construct($config);
        $this->actor_repository = $actor_repository;
        $this->role_repository = $role_repository;
        $this->type_repository = $type_repository;
        $this->permission_repository = $permission_repository;
        $this->request = $request;
    }

    /**
     * Get a list of all actors
     *
     * @return AResponse
     *
     * @throws SystemException
     */
	#[Route("")]
    public function index(): AResponse {
        $params = App::getInstanceOf(RequestHelper::class)->getPaginationParams();

        $pagination = "";
        HTMLHelper::getPagination( $params['page'],  $this->actor_repository->getNumRows(), $params['limit'], $pagination);

        $view = new Template(PATH_VIEWS."actor/index.html");
        $view->set("actor_list", $this->actor_repository->find(array(), $params['order'], $params['direction'], $params['limit'], $params['page']));
        $view->set("pagination", $pagination);

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

    /**
     * Searches the db for actors that mache the search string
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("search")]
    public function search(): AResponse {
        $search_string = $this->request->get("search_string");

        $results = array();
        if( $search_string !== null ) {
            $results = $this->actor_repository->find([
                ["first_name", "LIKE", '%'.$search_string.'%'],
                ["OR","last_name", "LIKE", '%'.$search_string.'%'],
                ["OR","email", "LIKE", '%'.$search_string.'%'],
            ]);
        }
        $view = new Template(PATH_VIEWS."actor/search.html");
        $view->set("search_string", $search_string);
        $view->set("actor_list", $results);

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * Shows a create form for an actor
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("create")]
    public function create(): AResponse {
		if( !App::$curr_actor_role->canCreateAll() ) {
			redirect("/error/403");
		} else if( App::$request->contains('create') ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$actor = new ActorModel();
                $this->setActorParams($actor);
                $this->actor_repository->createObject($actor);
				$this->savePermissions($actor);
				redirect("/actors");
			}
		}

        $routes = App::getInstanceOf(Router::class)->getSortedRoutes();
        $view = new Template(PATH_VIEWS."actor/create.html");
        $view->set("routes", $routes);
        $view->set("role_options", $this->role_repository->getAll());
        $view->set("type_options", $this->type_repository->getAll());
        $view->set("access_permissions", array());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

    /**
     * @param ActorModel $actor
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("{actor}")]
    public function update( ActorModel $actor ): AResponse {
		if( !App::$curr_actor_role->canUpdate($actor->id) ) {
			redirect("/error/403");
		} else if( App::$request->contains('cancel') ) {
			redirect("/actors");
		} else if( App::$request->contains('update') ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
                $this->setActorParams($actor);
                $this->actor_repository->updateObject($actor);
                redirect("/actors");
			}
		}

        $access_permissions = array();
        $view = new Template(PATH_VIEWS."actor/edit.html");
        $view->set("actor", $actor);
        $view->set("role_options", $this->role_repository->getAll());
        $view->set("type_options", $this->type_repository->getAll());
        $view->set("access_permissions", $access_permissions);

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
	}

    /**
     * @param ActorModel $actor
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("delete/{actor}")]
    public function delete( ActorModel $actor ): AResponse {
        if( !App::$curr_actor_role->canDelete($actor->id) ) {
            redirect("/error/403");
        } else if( App::$request->contains('cancel') ) {
            redirect("/actors");
        }
        $this->actor_repository->deleteObject($actor);
        redirect("/actors");
        return new HtmlResponse();
    }

    /**
     * @param ActorModel $actor
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("roles/{actor}")]
    public function roles( ActorModel $actor ): AResponse {
		if( !App::$curr_actor_role->canUpdate($actor->id) ) {
			redirect("/error/403");
		} else if( App::$request->contains('cancel') ) {
			redirect("/actors");
		} else if( App::$request->contains('update') ) {
			$this->savePermissions($actor);
			redirect("/actors");
		}

        $view = new Template(PATH_VIEWS."actor/roles.html");
        $view->set("actor", $actor);
        $view->set("routes", App::getInstanceOf(Router::class)->getSortedRoutes());
        $view->set("role_options", $this->role_repository->getAll());
        $view->set("access_permissions", $this->permission_repository->find([["actor_id", "=", $actor->id]]));

		$template = new Template(PATH_VIEWS."template.html");
	    $template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

    /**
     * Checks if all required values are setClass
     *
     * @return bool
     */
	private function postIsValid(): bool {
        if( !App::$request->contains('type_id') || (int)App::$request->get('type_id') === 0 ) {
            return false;
        }
		if( !App::$request->contains('email') || App::$request->get('email') === "" ) {
			return false;
		}
		if( !App::$request->contains('password') || ( App::$request->contains('create') && App::$request->get('password') === '' ) ) {
            return false;
		}
		if( !App::$request->contains('password') || !App::$request->contains('password_verify')
            || App::$request->get('password') !== App::$request->get('password_verify')
        ) {
            return false;
		}
		if( !App::$request->contains('first_name') || App::$request->get('first_name') === "" ) {
            return false;
		}
		if( !App::$request->contains('last_name') || App::$request->get('last_name') === "" ) {
            return false;
		}
		return true;
	}

    /**
     * Updates the given actor object with the posted values from the request
     *
     * @param ActorModel $actor
     *
     * @return void
     */
    private function setActorParams( Actor $actor ): void {
        $actor->type_id = (App::$request->contains('type_id'))
            ? (int)App::$request->get('type_id')
            : 0
        ;
        $actor->email = App::$request->get("email");
        $actor->password = App::$request->get('password');
        $actor->first_name = App::$request->get("first_name");
        $actor->last_name = App::$request->get("last_name");
        $actor->login_fails = (App::$request->contains('login_fails'))
            ? (int)App::$request->get('login_fails')
            : 0
        ;
        $actor->login_disabled = (App::$request->contains('login_disabled'))
            ? (int)App::$request->get('login_disabled')
            : 0
        ;
    }

    /**
     * Save the permissions for the given actor
     *
     * @param Actor $actor
     *
     * @return void
     *
     * @throws SystemException
     */
	private function savePermissions( Actor $actor ): void {
		if( $actor->id === 0 ) {
			return;
		}
		$roles = array();
		foreach( App::$request->get('role') as $domain => $entry_domain ) {
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

        $this->permission_repository->deleteAccessPermissionFor($actor);
        foreach( $roles as $domain => $controllers ) {
			foreach( $controllers as $controller => $methods ) {
				foreach( $methods as $method => $role ) {
					$access_permission = new AccessPermission();
                    $access_permission->actor_id = $actor->id;
                    $access_permission->role_id = $role;
                    $access_permission->domain = $domain;
                    $access_permission->controller = ( $controller !== '' ) ? $controller : null;
                    $access_permission->method = ( $method !== '' ) ? $method : null;
                    $this->permission_repository->createObject($access_permission);
				}
			}
		}
	}

}
