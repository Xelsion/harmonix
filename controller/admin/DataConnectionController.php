<?php
namespace controller\admin;

use lib\App;
use lib\classes\Template;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\core\response_types\HtmlResponse;
use models\DataConnectionModel;
use models\entities\DataConnectionColumn;

#[Route("data-connections")]
class DataConnectionController extends AController {

    /**
     * Get a list of all data connections
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("")]
    public function index(): AResponse {
        $view = new Template(PATH_VIEWS."data_connection/index.html");
        $view->set("result_list", DataConnectionModel::find());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("/create")]
    public function create(): AResponse {
        if( !App::$curr_actor_role->canCreateAll() ) {
            redirect("/error/403");
        }

        if( App::$request->data->contains('cancel') ) {
            redirect("/actor-types");
        }

        if( App::$request->data->contains('create') ) {
            $is_valid = $this->postIsValid();
            if( $is_valid ) {
                $data_connection = App::getInstanceOf(DataConnectionModel::class);
                $data_connection->name = App::$request->data->get('name');
                $data_connection->db_name = App::$request->data->get('db_name');
                $data_connection->table_name = App::$request->data->get('table_name');
                $data_connection->table_col = App::$request->data->get('table_col');
                $data_connection->create();
                foreach( App::$request->data->get('data_columns') as $col_name ) {
                    $data_column = App::getInstanceOf(DataConnectionColumn::class);
                    $data_column->connection_id = $data_connection->id;
                    $data_column->column_name = $col_name;
                    $data_column->create();
                }

                redirect("/data-connections");
            }
        }

        $cm = App::getInstanceOf(ConnectionManager::class);

        $view = new Template(PATH_VIEWS."data_connection/create.html");
        $view->set("available_connections", $cm->getAvailableConnections());

        $template = new Template(PATH_VIEWS."template.html");
        $template->set("view", $view->parse());

        return new HtmlResponse($template->parse());
    }

    /**
     * @return bool
     */
    private function postIsValid(): bool {
        if( !App::$request->data->contains('name') || App::$request->data->get('name') === "" ) {
            return false;
        }
        if( !App::$request->data->contains('db_name') || App::$request->data->get('db_name') === "" ) {
            return false;
        }
        if( !App::$request->data->contains('table_name') || App::$request->data->get('table_name') === "" ) {
            return false;
        }
        if( !App::$request->data->contains('table_col') || App::$request->data->get('table_col') === "" ) {
            return false;
        }
        if( !App::$request->data->contains('data_columns') || empty(App::$request->data->get('data_columns')) ) {
            return false;
        }
        return true;
    }
}