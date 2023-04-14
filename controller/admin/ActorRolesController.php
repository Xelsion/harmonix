<?php
namespace controller\admin;

use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\classes\Configuration;
use lib\core\classes\Template;
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
    public function __construct(ActorRoleRepository $role_repository,
                                Configuration       $config
    ) {
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
	#[Route("")]
    public function index(): AResponse {
		$view = new Template(PATH_VIEWS."actor_roles/index.html");
        $actor_roles_tree = App::getInstanceOf(RoleTree::class);
        $view->set("role_tree", $actor_roles_tree);

		$template = new Template(PATH_VIEWS."template.html");
		$template->set("view", $view->parse() );

		return new HtmlResponse($template->parse());
	}

    /**
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("create")]
    public function create(): AResponse {
        if( !App::$curr_actor_role->canCreateAll() ) {
            redirect("/error/403");
        } else if( App::$request->contains("create") ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
                $role = App::getInstanceOf(ActorRoleModel::class);
                $this->setRoleParams($role);
                $this->role_repository->createObject($role);
				redirect("/actor-roles");
			}
		}

        $view = new Template(PATH_VIEWS."actor_roles/create.html");
        $view->set("option_list", $this->role_repository->getAll());

		$template = new Template(PATH_VIEWS."template.html");
		$template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

    /**
     * @param ActorRoleModel $role
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("/{role}")]
    public function update( ActorRoleModel $role ): AResponse {
        if( !App::$curr_actor_role->canUpdateAll() ) {
            redirect("/error/403");
        } else if( App::$request->contains("cancel") ) {
			redirect("/actor-roles");
		} else if( App::$request->contains("update") ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
                $this->setRoleParams($role);
                $this->role_repository->updateObject($role);
				redirect("/actor-roles");
			}
		}
        $view = new Template(PATH_VIEWS."actor_roles/edit.html");
        $view->set("role", $role);
        $view->set("actor_role", App::$curr_actor_role);
        $view->set("option_list", $this->role_repository->find(array(["id", "!=", $role->id])));

		$template = new Template(PATH_VIEWS."template.html");
		$template->set("view", $view->parse());

		return new HTMLResponse($template->parse());
	}

    /**
     * @param ActorRoleModel $role
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("delete/{role}")]
    public function delete( ActorRoleModel $role ) : AResponse {
        if( !App::$curr_actor_role->canDeleteAll() ) {
            redirect("/error/403");
        } else if( App::$request->contains("cancel") ) {
            redirect("/actor-roles");
        } else if( App::$request->contains("delete") ) {
            $this->role_repository->deleteObject($role);
            redirect("/actor-roles");
        }

        $view = new Template(PATH_VIEWS . "actor_roles/delete.html");
        $view->set("role", $role);

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @param array $settings
     * @return int
     */
	private function getPermissions( array $settings ): int {
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
        return !(!App::$request->contains("name") || App::$request->get("name") === "");
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
        $role->child_of = ( App::$request->contains("child_of") && (int)App::$request->get("child_of") > 0 )
            ? (int)App::$request->get("child_of")
            : null
        ;
        $role->rights_all = ( App::$request->contains("all") )
            ? $this->getPermissions(App::$request->get("all"))
            : 0b000
        ;
        $role->rights_group = ( App::$request->contains("group") )
            ? $this->getPermissions(App::$request->get("group"))
            : 0b000
        ;
        $role->rights_own = ( App::$request->contains("own") )
            ? $this->getPermissions(App::$request->get("own"))
            : 0b000
        ;
    }
}
