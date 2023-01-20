<?php

namespace controller\admin;

use JsonException;
use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\responses\HtmlResponse;
use lib\classes\Template;
use lib\core\System;
use lib\exceptions\SystemException;
use models\DataConnectionModel;
use models\entities\DataConnectionColumn;

#[Route("data-connections")]
class DataConnectionController extends AController {

    /**
     * Get a list of all data connections
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("")]
    public function index(): AResponse {
        $view = new Template(PATH_VIEWS."data_connection/index.html");
        $view->set("result_list", DataConnectionModel::find());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return AResponse
     *
     * @throws JsonException
     * @throws SystemException
     */
    #[Route("/create")]
    public function create(): AResponse {
        if( !System::$Core->actor_role->canCreateAll() ) {
            redirect("/error/403");
        }

        if( isset($_POST['cancel']) ) {
            redirect("/actor-types");
        }

        if( isset($_POST['create']) ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $data_connection = new DataConnectionModel();
                $data_connection->name = $_POST["name"];
                $data_connection->db_name = $_POST["db_name"];
                $data_connection->table_name = $_POST["table_name"];
                $data_connection->table_col = $_POST["table_col"];
                $data_connection->create();
                foreach( $_POST["data_columns"] as $col_name ) {
                    $data_column = new DataConnectionColumn();
                    $data_column->connection_id = $data_connection->id;
                    $data_column->column_name = $col_name;
                    $data_column->create();
                }

                redirect("/data-connections");
            }
        }

        $view = new Template(PATH_VIEWS."data_connection/create.html");

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("navigation", System::$Core->menu);
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    private function postIsValid(): bool {
        if( !isset($_POST["name"]) || $_POST["name"] === "" ) {
            return false;
        }
        if( !isset($_POST["db_name"]) || $_POST["db_name"] === "" ) {
            return false;
        }
        if( !isset($_POST["table_name"]) || $_POST["table_name"] === "" ) {
            return false;
        }
        if( !isset($_POST["table_col"]) || $_POST["table_col"] === "" ) {
            return false;
        }
        if( empty($_POST["data_columns"]) ) {
            return false;
        }
        return true;
    }
}