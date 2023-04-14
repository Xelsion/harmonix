<?php
namespace models;

use Exception;
use lib\App;
use lib\core\ConnectionManager;
use lib\core\database\QueryBuilder;
use lib\core\exceptions\SystemException;
use lib\helper\MySqlHelper;
use PDO;
use repositories\AccessRestrictionTypeRepository;

/**
 * The AccessRestrictionTypeModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessRestrictionTypeModel extends entities\AccessRestrictionType {

    private readonly AccessRestrictionTypeRepository $restriction_type_repository;

    /**
     * The class constructor
     * If id is 0 it will return an empty actor
     *
     * @param int $id
     *
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        $this->restriction_type_repository = App::getInstanceOf(AccessRestrictionTypeRepository::class);

        if( $id > 0 ) {
            try {
                $restriction_type_data = $this->restriction_type_repository->getAsArray($id);
                if( !empty($restriction_type_data) ) {
                    $this->id = (int)$restriction_type_data["id"];
                    $this->name = $restriction_type_data["name"];
                    $this->include_siblings = (int)$restriction_type_data["include_siblings"];
                    $this->include_children = (int)$restriction_type_data["include_children"];
                    $this->include_descendants = (int)$restriction_type_data["include_descendants"];
                    $this->created = $restriction_type_data["created"];
                    $this->updated = ( $restriction_type_data["updated"] !== "" ) ? $restriction_type_data["updated"] : null;
                    $this->deleted = ( $restriction_type_data["deleted"] !== "" ) ? $restriction_type_data["deleted"] : null;
                }
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

}
