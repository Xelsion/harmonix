<?php
namespace controller\admin;

use lib\App;
use lib\classes\Template;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use models\ActorRoleModel;

/**
 * @see \lib\core\blueprints\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("actor-roles")]
class ActorRolesController extends AController {

    /**
     * Get a list of all actor roles
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
	#[Route("")]
    public function index(): AResponse {
		$view = new Template(PATH_VIEWS."actor_roles/index.html");
        $view->set("result_list", ActorRoleModel::find());

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
		if( App::$request->data->contains("create") ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
                $role = App::getInstanceOf(ActorRoleModel::class);
                $this->setRoleParams($role);
				$role->create();
				redirect("/actor-roles");
			}
		}
        $view = new Template(PATH_VIEWS."actor_roles/create.html");
        $view->set("option_list", ActorRoleModel::find());

		$template = new Template(PATH_VIEWS."template.html");
		$template->set("view", $view->parse());

		return new HtmlResponse($template->parse());
	}

    /**
     * @param ActorRoleModel $role
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("/{role}")]
    public function update( ActorRoleModel $role ): AResponse {
		if( App::$request->data->contains("cancel") ) {
			redirect("/actor-roles");
		}
		if( App::$request->data->contains("update") ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
                $this->setRoleParams($role);
				$role->update();
				redirect("/actor-roles");
			}
		}
        $view = new Template(PATH_VIEWS."actor_roles/edit.html");
        $view->set("role", $role);
        $view->set("actor_role", App::$curr_actor_role);
        $view->set("option_list", ActorRoleModel::find(array(["id", "!=", $role->id])));

		$template = new Template(PATH_VIEWS."template.html");
		$template->set("view", $view->parse());

		return new HTMLResponse($template->parse());
	}

    /**
     * @param ActorRoleModel $role
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("delete/{role}")]
    public function delete( ActorRoleModel $role ) : AResponse {
        if( App::$request->data->contains("cancel") ) {
            redirect("/actor-roles");
        }
        if( App::$request->data->contains("delete") ) {
            $role->delete();
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
		if( !App::$request->data->contains("name") || App::$request->data->get("name") === "" ) {
			return false;
		}
		return true;
	}

    /**
     * Sets the actor role parameters of the given role
     *
     * @param ActorRoleModel $role
     *
     * @return void
     */
    private function setRoleParams(ActorRoleModel &$role): void {
        $role->name = App::$request->data->get("name");
        $role->child_of = ( App::$request->data->contains("child_of") && intval(App::$request->data->get("child_of")) > 0 )
            ? intval(App::$request->data->get("child_of"))
            : null
        ;
        $role->rights_all = ( App::$request->data->contains("all") )
            ? $this->getPermissions(App::$request->data->get("all"))
            : 0b000
        ;
        $role->rights_group = ( App::$request->data->contains("group") )
            ? $this->getPermissions(App::$request->data->get("group"))
            : 0b000
        ;
        $role->rights_own = ( App::$request->data->contains("own") )
            ? $this->getPermissions(App::$request->data->get("own"))
            : 0b000
        ;;
    }
}
