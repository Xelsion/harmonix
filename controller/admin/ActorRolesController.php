<?php

namespace controller\admin;

use Exception;
use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\responses\HtmlResponse;
use lib\classes\Template;
use lib\core\System;
use lib\exceptions\SystemException;
use models\ActorRoleModel;

/**
 * @see \lib\abstracts\AController
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
#[Route("actor-roles")]
class ActorRolesController extends AController {

    /**
     * Get a list of all actor roles
     *
     * @throws Exception
     */
	#[Route("")]
    public function index(): AResponse {
		$response = new HtmlResponse();
		$template = new Template(PATH_VIEWS."template.html");

		$template->set("navigation", System::$Core->menu);
		$template->set("result_list", ActorRoleModel::find());
		$template->set("view", new Template(PATH_VIEWS."actor_roles/index.html"));
		$response->setOutput($template->parse());
		return $response;
	}

    /**
     * @throws Exception
     */
    #[Route("create")]
    public function create(): AResponse {
		if( isset($_POST['create']) ) {
			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$all = ( isset($_POST["all"]) ) ? $this->getPermissions($_POST["all"]) : 0b000;
				$group = ( isset($_POST["group"]) ) ? $this->getPermissions($_POST["group"]) : 0b000;
				$own = ( isset($_POST["own"]) ) ? $this->getPermissions($_POST["own"]) : 0b000;
				$role = new ActorRoleModel();
				$role->name = $_POST["name"];
				$role->child_of = ( (int)$_POST["child_of"] > 0 ) ? (int)$_POST["child_of"] : null;
				$role->rights_all = $all;
				$role->rights_group = $group;
				$role->rights_own = $own;
				$role->create();
				redirect("/actor-roles");
			}
		}

		$response = new HtmlResponse();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("navigation", System::$Core->menu);
		$template->set("option_list", ActorRoleModel::find());
		$template->set("view", new Template(PATH_VIEWS."actor_roles/create.html"));
		$response->setOutput($template->parse());
		return $response;
	}

    /**
     * @throws Exception
     */
    #[Route("/{role}")]
    public function update( ActorRoleModel $role ): AResponse {
		if( isset($_POST['cancel']) ) {
			redirect("/actor-roles");
		}
		if( isset($_POST['update']) ) {

			$is_valid = $this->postIsValid();
			if( $is_valid ) {
				$all = ( isset($_POST["all"]) ) ? $this->getPermissions($_POST["all"]) : 0b000;
				$group = ( isset($_POST["group"]) ) ? $this->getPermissions($_POST["group"]) : 0b000;
				$own = ( isset($_POST["own"]) ) ? $this->getPermissions($_POST["own"]) : 0b000;
				$role->name = $_POST["name"];
				$role->child_of = ( (int)$_POST["child_of"] > 0 ) ? (int)$_POST["child_of"] : null;
				$role->rights_all = $all;
				$role->rights_group = $group;
				$role->rights_own = $own;
				$role->update();
				redirect("/actor-roles");
			}
		}

		$response = new HtmlResponse();
		$template = new Template(PATH_VIEWS."template.html");
		$template->set("role", $role);
		$template->set("option_list", ActorRoleModel::find(array(["id", "!=", $role->id])));
		$template->set("navigation", System::$Core->menu);
		$template->set("view", new Template(PATH_VIEWS."actor_roles/edit.html"));
		$response->setOutput($template->parse());
		return $response;
	}

    /**
     * @throws SystemException
     */
    #[Route("delete")]
    public function delete( ActorRoleModel $role ) : AResponse {
        if( isset($_POST['cancel']) ) {
            redirect("/actor-roles");
        }
        if( isset($_POST['delete']) ) {
            try {
                $role->delete();
                redirect("/actor-roles");
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
            }
        }

        $view = new Template(PATH_VIEWS . "actor_roles/delete.html");

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("role", $role);
        $template->set("navigation", System::$Core->menu);
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
		$is_valid = true;
		if( !isset($_POST["name"]) || $_POST["name"] === "" ) {
			$is_valid = false;
		}
		return $is_valid;
	}
}
