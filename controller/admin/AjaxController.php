<?php
namespace controller\admin;

use Exception;
use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\blueprints\AResponse;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\core\response_types\JsonResponse;

#[Route("ajax")]
class AjaxController extends AController {

    /**
     * @param string $db_name
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("getInstanceOf-tables/{db_name}")]
    public function getTables( string $db_name ): AResponse {
        $response = new JsonResponse();
        try {
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $tables = $pdo->getTables();
            $response->setOutput($tables);
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage());
        }

        return $response;
    }

    /**
     * @param string $db_name
     * @param string $table_name
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("getInstanceOf-table-key-columns/{db_name}/{table_name}")]
    public function getTableKeyColumns( string $db_name, string $table_name ): AResponse {
        $response = new JsonResponse();

        try {
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $columns = $pdo->getTableKeyColumns( $table_name );
            $response->setOutput($columns);
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage());
        }
        return $response;
    }

    /**
     * @param string $db_name
     * @param string $table_name
     *
     * @return AResponse
     *
     * @throws \lib\core\exceptions\SystemException
     */
    #[Route("getInstanceOf-table-columns/{db_name}/{table_name}")]
    public function getTableColumns( string $db_name, string $table_name ): AResponse {
        $response = new JsonResponse();

        try {
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $columns = $pdo->getTableColumns( $table_name );
            $response->setOutput($columns);
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage());
        }
        return $response;
    }

}