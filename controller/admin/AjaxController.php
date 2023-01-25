<?php
namespace controller\admin;

use lib\App;
use lib\abstracts\AController;
use lib\abstracts\AResponse;
use lib\attributes\Route;
use lib\classes\responses\JsonResponse;
use lib\manager\ConnectionManager;

use Exception;
use lib\exceptions\SystemException;

#[Route("ajax")]
class AjaxController extends AController {

    /**
     * @param string $db_name
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("getInstance-tables/{db_name}")]
    public function getTables( string $db_name ): AResponse {
        $response = new JsonResponse();
        try {
            $cm = App::getInstance(ConnectionManager::class);
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
     * @throws SystemException
     */
    #[Route("getInstance-table-key-columns/{db_name}/{table_name}")]
    public function getTableKeyColumns( string $db_name, string $table_name ): AResponse {
        $response = new JsonResponse();

        try {
            $cm = App::getInstance(ConnectionManager::class);
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
     * @throws SystemException
     */
    #[Route("getInstance-table-columns/{db_name}/{table_name}")]
    public function getTableColumns( string $db_name, string $table_name ): AResponse {
        $response = new JsonResponse();

        try {
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $columns = $pdo->getTableColumns( $table_name );
            $response->setOutput($columns);
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage());
        }
        return $response;
    }

}