<?php
namespace controller\admin;

use DateTime;
use lib\App;
use lib\classes\Template;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\cache\types\ResponseCache;
use lib\core\classes\Configuration;
use lib\core\ConnectionManager;
use lib\core\Request;
use lib\core\response_types\HtmlResponse;
use lib\core\Router;
use lib\helper\HtmlHelper;
use lib\helper\RequestHelper;
use models\AccessPermissionModel;
use models\ActorModel;
use models\ActorRoleModel;
use models\ActorTypeModel;
use models\DataConnectionModel;
use models\entities\ActorData;
use PDO;

/**
 * @see \lib\core\blueprints\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("actors")]
class ActorController extends AController {

    public function __construct( private readonly Request $request, Configuration $config ) {
        parent::__construct($config);
    }

    /**
     * Get a list of all actors
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
	#[Route("")]
    public function index(): AResponse {
        $params = App::getInstanceOf(RequestHelper::class)->getPaginationParams();

        $cache = App::getInstanceOf(ResponseCache::class);
        $cache->initCacheFor(__METHOD__, ...$params);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."template.html");
        $cache->addFileCheck(PATH_VIEWS."actor/index.html");
        if( self::$caching && $cache->isUpToDate() ) {
            $content = $cache->getContent();
        } else {
            $pagination = "";
            HTMLHelper::getPagination( $params['page'],  ActorModel::getNumActors(), $params['limit'], $pagination);

            $view = new Template(PATH_VIEWS."actor/index.html");
            $view->set("actor_list", ActorModel::find(array(), $params['order'], $params['direction'], $params['limit'], $params['page']));
            $view->set("pagination", $pagination);

            $template = new Template(PATH_VIEWS."template.html");
            $template->set("view", $view->parse());

            $content = $template->parse();

            // if caching is enabled write the generated output into the cache file
            if(self::$caching) {
                $cache->saveContent($content);
            }

        }

		return new HtmlResponse($content);
	}

    /**
     * Searches the db for actors that mache the search string
     *
     * @return \lib\core\blueprints\AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("search")]
    public function search(): AResponse {
        $search_string = $this->request->data->get("search_string");

        $params = array($search_string);
        $cache = App::getInstanceOf(ResponseCache::class);
        $cache->initCacheFor(__METHOD__, ...$params);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."template.html");
        $cache->addFileCheck(PATH_VIEWS."actor/search.html");

        if( self::$caching && $cache->isUpToDate() ) {
            $content = $cache->getContent();
        } else {
            $results = array();
            if( $search_string !== null ) {
                $results = ActorModel::find([
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

            $content = $template->parse();

            // if caching is enabled write the generated output into the cache file
            if( self::$caching ) {
                $cache->saveContent($content);
            }
        }

        return new HtmlResponse($content);
    }

    /**
     * Shows a create form for an actor
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("create")]
    public function create(): AResponse {
		if( !App::$curr_actor_role->canCreateAll() ) {
			redirect("/error/403");
		}

		if( App::$request->data->contains('create') ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$actor = App::getInstanceOf(ActorModel::class);
                $this->setActorParams($actor);
				$actor->create();
				$this->savePermissions($actor);
				redirect("/actors");
			}
		}

        $cache = App::getInstanceOf(ResponseCache::class);
        $cache->initCacheFor(__METHOD__);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."template.html");
        $cache->addFileCheck(PATH_VIEWS."actor/create.html");
        $cache->addFileCheck(PATH_VIEWS."snippets/access_permissions.html");
        $cache->addDBCheck("mvc", "actor_roles");
        $cache->addDBCheck("mvc", "actor_types");

        if( self::$caching && $cache->isUpToDate() ) {
            $content = $cache->getContent();
        } else {
            $routes = App::getInstanceOf(Router::class)->getSortedRoutes();
            $view = new Template(PATH_VIEWS."actor/create.html");
            $view->set("routes", $routes);
            $view->set("role_options", ActorRoleModel::find());
            $view->set("type_options", ActorTypeModel::find());
            $view->set("access_permissions", array());

            $template = new Template(PATH_VIEWS."template.html");
            $template->set("view", $view->parse());

            $content = $template->parse();

            // if caching is enabled write the generated output into the cache file
            if(self::$caching) {
                $cache->saveContent($content);
            }
        }

		return new HtmlResponse($content);
	}

    /**
     * @param ActorModel $actor
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("{actor}")]
    public function update( ActorModel $actor ): AResponse {
		if( !App::$curr_actor_role->canUpdate($actor->id) ) {
			redirect("/error/403");
		}

		if( App::$request->data->contains('cancel') ) {
			redirect("/actors");
		}

		if( App::$request->data->contains('update') ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
                $this->setActorParams($actor);
				$actor->update();
                $data = App::$request->data->get('actor_data');
                foreach( $data as $index => $entry ) {
                    $key = $entry["key"];
                    $value = $entry["value"];
                    if( strlen($key) > 0 && strlen($value) > 0 ) {
                        $connection_id = ( intval($entry["connection"]) > 0 ) ? intval($entry["connection"]) : null;
                        if( $index === 0 ) {
                            $actor_data = App::getInstanceOf(ActorData::class);
                            $actor_data->actor_id = $actor->id;
                            $actor_data->data_key = $key;
                            $actor_data->data_value = $value;
                            $actor_data->connection_id = $connection_id;
                            $actor_data->create();
                        } else {
                            $actor_data = App::getInstanceOf(ActorData::class, null, ["id" => $index]);
                            $actor_data->data_key = $key;
                            $actor_data->data_value = $value;
                            $actor_data->connection_id = $connection_id;
                            $actor_data->update();
                        }
                    }
                }
                redirect("/actors");
			}
		}

        $access_permissions = array();
        $view = new Template(PATH_VIEWS."actor/edit.html");
        $view->set("actor", $actor);
        $view->set("role_options", ActorRoleModel::find());
        $view->set("type_options", ActorTypeModel::find());
        $view->set("connection_options", DataConnectionModel::find());
        $view->set("access_permissions", $access_permissions);

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
	}

    /**
     * @param ActorModel $actor
     *
     * @return \lib\core\blueprints\AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("delete/{actor}")]
    public function delete( ActorModel $actor ): AResponse {
        if( !App::$curr_actor_role->canDelete($actor->id) ) {
            redirect("/error/403");
        }

        if( App::$request->data->contains('cancel') ) {
            redirect("/actors");
        }

        $delete_date = new DateTime();
        $actor->deleted = $delete_date->format('Y-m-d H:i:s');
        $actor->update();
        redirect("/actors");
        return new HtmlResponse();
    }

    /**
     * @param ActorModel $actor
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("roles/{actor}")]
    public function roles( ActorModel $actor ): AResponse {
		if( !App::$curr_actor_role->canUpdate($actor->id) ) {
			redirect("/error/403");
		}

		if( App::$request->data->contains('cancel') ) {
			redirect("/actors");
		}

		if( App::$request->data->contains('update') ) {
			$this->savePermissions($actor);
			redirect("/actors");
		}

        $view = new Template(PATH_VIEWS."actor/roles.html");
        $view->set("actor", $actor);
        $view->set("routes", App::getInstanceOf(Router::class)->getSortedRoutes());
        $view->set("role_options", ActorRoleModel::find());
        $view->set("access_permissions", AccessPermissionModel::find(array(
            ["actor_id", "=", $actor->id]
        )));

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
		if( !App::$request->data->contains('email') || App::$request->data->get('email') === "" ) {
			return false;
		}
		if( !App::$request->data->contains('password') || ( App::$request->data->contains('create') && App::$request->data->get('password') === '' ) ) {
            return false;
		}
		if( !App::$request->data->contains('password') || !App::$request->data->contains('password_verify')
            || App::$request->data->get('password') !== App::$request->data->get('password_verify')
        ) {
            return false;
		}
		if( !App::$request->data->contains('first_name') || App::$request->data->get('first_name') === "" ) {
            return false;
		}
		if( !App::$request->data->contains('last_name') || App::$request->data->get('last_name') === "" ) {
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
    private function setActorParams( ActorModel $actor ): void {
        $actor->type_id = (App::$request->data->contains('type_id'))
            ? (int)App::$request->data->get('type_id')
            : 0
        ;
        $actor->email = App::$request->data->get("email");
        $actor->password = App::$request->data->get('password');
        $actor->first_name = App::$request->data->get("first_name");
        $actor->last_name = App::$request->data->get("last_name");
        $actor->login_fails = (App::$request->data->contains('login_fails'))
            ? (int)App::$request->data->get('login_fails')
            : 0
        ;
        $actor->login_disabled = (App::$request->data->contains('login_disabled'))
            ? (int)App::$request->data->get('login_disabled')
            : 0
        ;
    }

    /**
     * Save the permissions for the given actor
     *
     * @param ActorModel $actor
     *
     * @return void
     *
     * @throws \lib\core\exceptions\SystemException
     */
	private function savePermissions( ActorModel $actor ): void {
		if( $actor->id === 0 ) {
			return;
		}
		$roles = array();
		foreach( App::$request->data->get('role') as $domain => $entry_domain ) {
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
					$actor_permission = new AccessPermissionModel();
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

}
