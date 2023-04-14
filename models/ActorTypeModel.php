<?php
namespace models;

use Exception;
use lib\App;
use lib\core\ConnectionManager;
use lib\core\database\QueryBuilder;
use lib\core\exceptions\SystemException;
use lib\helper\MySqlHelper;
use PDO;
use repositories\ActorTypeRepository;

/**
 * The ActorTypeModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorTypeModel extends entities\ActorType {

    private readonly ActorTypeRepository $type_repository;

    /**
     * The class constructor
     *
     * @param int $id
     *
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        $this->type_repository = App::getInstanceOf(ActorTypeRepository::class);
        if( $id > 0 ) {
            try {
                $type_data = $this->type_repository->getAsArray($id);
                if( !empty($type_data) ) {
                    $this->id = (int)$type_data["id"];
                    $this->name = $type_data["name"];
                    $this->is_protected = (bool)$type_data["is_protected"];
                    $this->created = $type_data["created"];
                    $this->updated = ( $type_data["updated"] !== "" ) ? $type_data["updated"] : null;
                    $this->deleted = ( $type_data["deleted"] !== "" ) ? $type_data["deleted"] : null;
                }
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

}
