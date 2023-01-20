<?php

namespace controller\admin;

use DateTime;
use Exception;
use JsonException;
use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\responses\HtmlResponse;
use lib\classes\Template;
use lib\core\System;
use lib\exceptions\SystemException;
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
 * @see \lib\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("actors")]
class ActorController extends AController {

    /**
     * Get a list of all actors
     *
     * @throws Exception
     * @throws JsonException
     * @throws SystemException
     */
	#[Route("")]
    public function index(): AResponse {
        $params = RequestHelper::getPaginationParams();

        $cache = System::$Core->response_cache;
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
            $view->set("result_list", ActorModel::find(array(), $params['order'], $params['direction'], $params['limit'], $params['page']));
            $view->set("pagination", $pagination);

            $template = new Template(PATH_VIEWS."template.html");
            $template->set("navigation", System::$Core->menu);
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
     * @return AResponse
     *
     * @throws JsonException
     * @throws SystemException
     */
    #[Route("search")]
    public function search(): AResponse {
        $search_string = System::$Core->request->get("search_string");

        $params = array($search_string);
        $cache = System::$Core->response_cache;
        $cache->initCacheFor(__METHOD__, ...$params);
        $cache->addFileCheck(__FILE__);
        $cache->addFileCheck(PATH_VIEWS."template.html");
        $cache->addFileCheck(PATH_VIEWS."actor/search.html");

        if( self::$caching && $cache->isUpToDate() ) {
            $content = $cache->getContent();
        } else {
            $results = array();
            if( $search_string !== null ) {
                $pdo = System::$Core->connection_manager->getConnection("mvc");
                $sql = "SELECT * FROM actors WHERE first_name LIKE :word OR last_name LIKE :word OR email LIKE :word";
                $pdo->prepareQuery($sql);
                $pdo->bindParam("word", "%".$search_string."%");
                $pdo->setFetchMode(PDO::FETCH_CLASS, ActorModel::class );
                $results = $pdo->execute()->fetchAll();
            }
            $view = new Template(PATH_VIEWS."actor/search.html");
            $view->set("search_string", $search_string);
            $view->set("result_list", $results);

            $template = new Template(PATH_VIEWS."template.html");
            $template->set("navigation", System::$Core->menu);
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
     * Shows a create form for an actor
     *
     * @throws Exception
     */
    #[Route("create")]
    public function create(): AResponse {
		if( !System::$Core->actor_role->canCreateAll() ) {
			redirect("/error/403");
		}

		if( isset($_POST['create']) ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$actor = new ActorModel();
				$actor->email = $_POST["email"];
				$actor->password = $_POST["password"];
				$actor->first_name = $_POST["first_name"];
				$actor->last_name = $_POST["last_name"];
				$actor->create();
				$this->savePermissions($actor);
				redirect("/actors");
			}
		}

        $cache = System::$Core->response_cache;
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
            $routes = System::$Core->router->getSortedRoutes();
            $view = new Template(PATH_VIEWS."actor/create.html");
            $view->set("routes", $routes);
            $view->set("role_options", ActorRoleModel::find());
            $view->set("type_options", ActorTypeModel::find());
            $view->set("access_permissions", array());

            $template = new Template(PATH_VIEWS."template.html");
            $template->set("navigation", System::$Core->menu);
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
     * @throws SystemException
     * @throws JsonException
     */
    #[Route("{actor}")]
    public function update( ActorModel $actor ): AResponse {
		if( !System::$Core->actor_role->canUpdate($actor->id) ) {
			redirect("/error/403");
		}

		if( isset($_POST['cancel']) ) {
			redirect("/actors");
		}

		if( isset($_POST['update']) ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
                $actor->type_id = (int)$_POST["type_id"];
				$actor->email = $_POST["email"];
				$actor->password = $_POST["password"];
				$actor->first_name = $_POST["first_name"];
				$actor->last_name = $_POST["last_name"];
                $actor->login_fails = (int) $_POST["login_fails"];
                $actor->login_disabled = (int) $_POST["login_disabled"];
				$actor->update();

                foreach( $_POST["actor_data"] as $index => $entry ) {
                    $key = $entry["key"];
                    $value = $entry["value"];
                    $connection_id = intval($entry["connection"]);
                    if( $index === 0 ) {
                        $actor_data = new ActorData();
                        $actor_data->actor_id = $actor->id;
                        $actor_data->data_key = $key;
                        $actor_data->data_value = $value;
                        $actor_data->connection_id = $connection_id;
                        $actor_data->create();
                    } else {
                        $actor_data = new ActorData($index);
                        $actor_data->data_key = $key;
                        $actor_data->data_value = $value;
                        $actor_data->connection_id = $connection_id;
                        $actor_data->update();
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
        $template->set("navigation", System::$Core->menu);
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
	}

    /**
     * @param ActorModel $actor
     *
     * @return AResponse
     *
     * @throws JsonException
     * @throws SystemException
     */
    #[Route("delete/{actor}")]
    public function delete( ActorModel $actor ): AResponse {
        if( !System::$Core->actor_role->canDelete($actor->id) ) {
            redirect("/error/403");
        }

        if( isset($_POST['cancel']) ) {
            redirect("/actors");
        }

        $delete_date = new DateTime();
        $actor->deleted = $delete_date->format('Y-m-d H:i:s');
        $actor->update();
        redirect("/actors");
        return new HtmlResponse();
    }

    /**
     * @throws SystemException
     * @throws Exception
     */
    #[Route("roles/{actor}")]
    public function roles( ActorModel $actor ): AResponse {
		if( !System::$Core->actor_role->canUpdate($actor->id) ) {
			redirect("/error/403");
		}

		if( isset($_POST['cancel']) ) {
			redirect("/actors");
		}

		if( isset($_POST['update']) ) {
			$this->savePermissions($actor);
			redirect("/actors");
		}

        $view = new Template(PATH_VIEWS."actor/roles.html");
        $view->set("actor", $actor);
        $view->set("routes", System::$Core->router->getSortedRoutes());
        $view->set("role_options", ActorRoleModel::find());
        $view->set("access_permissions", AccessPermissionModel::find(array(
            ["actor_id", "=", $actor->id]
        )));

		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", System::$Core->menu);
	    $template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

    /**
     * Checks if all required values are set
     *
     * @return bool
     */
	private function postIsValid(): bool {
		if( !isset($_POST["email"]) || $_POST["email"] === "" ) {
			return false;
		}
		if( !isset($_POST["password"]) || ( isset($_POST["create"]) && $_POST["password"] === '' ) ) {
            return false;
		}
		if( !array_key_exists("password", $_POST) || !array_key_exists("password_verify", $_POST) || $_POST["password"] !== $_POST["password_verify"] ) {
            return false;
		}
		if( !isset($_POST["first_name"]) || $_POST["first_name"] === "" ) {
            return false;
		}
		if( !isset($_POST["last_name"]) || $_POST["last_name"] === "" ) {
            return false;
		}
		return true;
	}

    /**
     * Save the permissions for the given actor
     *
     * @param ActorModel $actor
     * @return void
     *
     * @throws JsonException
     * @throws SystemException
     */
	private function savePermissions( ActorModel $actor ): void {
		if( $actor->id === 0 ) {
			return;
		}
		$roles = array();
		foreach( $_POST['role'] as $domain => $entry_domain ) {
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
