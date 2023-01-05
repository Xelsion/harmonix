<?php

namespace controller\admin;

use Exception;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\attributes\Route;
use system\classes\responses\JsonResponse;
use system\classes\Router;
use system\exceptions\SystemException;
use system\System;

#[Route("ajax")]
class AjaxController extends AController {

    /**
     * @param string $db_name
     *
     * @return AResponse
     *
     * @throws SystemException
     */
    #[Route("get-tables/{db_name}")]
    public function getTables( string $db_name ): AResponse {
        $response = new JsonResponse();

        try {
            $pdo = System::$Core->connection_manager->getConnection($db_name);
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
    #[Route("get-table-key-columns/{db_name}/{table_name}")]
    public function getTableKeyColumns( string $db_name, string $table_name ): AResponse {
        $response = new JsonResponse();

        try {
            $pdo = System::$Core->connection_manager->getConnection($db_name);
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
    #[Route("get-table-columns/{db_name}/{table_name}")]
    public function getTableColumns( string $db_name, string $table_name ): AResponse {
        $response = new JsonResponse();

        try {
            $pdo = System::$Core->connection_manager->getConnection($db_name);
            $columns = $pdo->getTableColumns( $table_name );
            $response->setOutput($columns);
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage());
        }
        return $response;
    }

}