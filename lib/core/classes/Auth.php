<?php
namespace lib\core\classes;

use Exception;
use lib\App;
use lib\core\blueprints\AController;
use lib\core\ConnectionManager;
use lib\core\enums\ActorRole;
use lib\core\exceptions\SystemException;
use lib\core\Request;
use lib\core\Router;
use models\AccessRestrictionTypeModel;
use models\ActorRoleModel;
use PDOStatement;

/**
 * The Auth class
 * checks if the current actor role contains access to the given route
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Auth {

    private ActorRoleModel $actor_role;
    private ActorRoleModel $restriction_role;
    private AccessRestrictionTypeModel $restriction_type;
    private array $role_cache = array();
    private array $type_cache = array();

    /**
     * The class constructor
     *
     * @throws \lib\core\exceptions\SystemException
     */
    public function __construct( protected readonly Request $request ) {
        $this->actor_role = App::$curr_actor_role;
        $route = App::getInstanceOf(Router::class)->getRoute($request);
        $restriction = $this->getRestriction(get_class($route["controller"]), $route["method"]);
        $this->restriction_role = $restriction["role"];
        $this->restriction_type = $restriction["type"];
    }

    /**
     * Returns if the current actor contains access to the current request
     *
     * @return bool
     *
     * @throws SystemException
     */
    public function hasAccess() : bool {
        return $this->getAccessibility($this->actor_role, $this->restriction_role, $this->restriction_type);
    }

    /**
     * Returns if the current actor contains access to the given controller a method
     *
     * @param $controller
     * @param string $method
     * @param string|mixed $domain
     *
     * @return bool
     *
     * @throws SystemException
     */
    public function hasAccessTo( $controller, string $method, string $domain = SUB_DOMAIN) : bool {
        if( $controller instanceof AController ) {
            $controller = get_class($controller);
        }
        $actor_role = App::$curr_actor->getRole($controller, $method, $domain);
        $restriction = $this->getRestriction($controller, $method, $domain);
        $restriction_role = $restriction["role"];
        $restriction_type = $restriction["type"];
        return $this->getAccessibility($actor_role, $restriction_role, $restriction_type);
    }

    /**
     * Returns if the actor role contains access to the restriction role
     *
     * @param ActorRoleModel $actor_role
     * @param ActorRoleModel $restriction_role
     * @param AccessRestrictionTypeModel $restriction_type
     *
     * @return bool
     *
     * @throws SystemException
     */
    private function getAccessibility( ActorRoleModel $actor_role, ActorRoleModel $restriction_role, AccessRestrictionTypeModel $restriction_type ) : bool {
        if( $restriction_role->id === ActorRole::Guest->value ) {
            return true;
        }

        if( $actor_role->isAncestorOf($restriction_role) ) {
            return true;
        }

        if( $restriction_role->id === $actor_role->id ) {
            return true;
        }

        if( $restriction_type->include_siblings === 1 && $actor_role->isSiblingOf($restriction_role) ) {
            return true;
        }

        if( $restriction_type->include_children === 1 && $actor_role->isChildOf($restriction_role)) {
            return true;
        }

        if( $restriction_type->include_descendants === 1 && $actor_role->isDescendantOf($restriction_role) ) {
            return true;
        }
        return false;
    }

    /**
     * Returns the restriction role and type for the given controller and method
     *
     * @param string|null $controller
     * @param string|null $method
     * @param string|mixed $domain
     *
     * @return array
     *
     * @throws SystemException
     */
    private function getRestriction(?string $controller, ?string $method, string $domain = SUB_DOMAIN ) : array {
        $result = array();

        $pdo_results = $this->getRestrictionRole( $controller, $method, $domain );
        if( $pdo_results->rowCount() === 1 ) {
            $row = $pdo_results->fetch();
            return $this->getResult($row);
        }

        $pdo_results = $this->getRestrictionRole( $controller, null, $domain );
        if( $pdo_results->rowCount() === 1 ) {
            $row = $pdo_results->fetch();
            return $this->getResult($row);
        }

        $pdo_results = $this->getRestrictionRole( null, null, $domain );
        if( $pdo_results->rowCount() === 1 ) {
            $row = $pdo_results->fetch();
            return $this->getResult($row);
        }

        $result["role"] = App::getInstanceOf(ActorRoleModel::class, null, ["id" => ActorRole::Guest->value]);
        $result["type"] = App::getInstanceOf(AccessRestrictionTypeModel::class, null, ["id" => ActorRole::Guest->value]);
        return $result;
    }

    /**
     * Returns the actor role and access restriction type of the given restriction role
     * Already used roles and restriction types will be get from the internal cache array
     *
     * @param array $row
     *
     * @return array
     *
     * @throws SystemException
     */
    private function getResult( array $row ): array {
        $role = App::getInstanceOf(ActorRoleModel::class, null, ["id" => $row["role_id"]]);
        $type = App::getInstanceOf(AccessRestrictionTypeModel::class, null, ["id" => $row["restriction_type"]]);
        return [ "role" => $role, "type" => $type ];
    }


    /**
     * Returns a SQL statement with the restriction role id and type id for the given controller and method
     *
     * @param string|null $controller
     * @param string|null $method
     * @param string|mixed $domain
     *
     * @return PDOStatement
     *
     * @throws SystemException
     */
    private function getRestrictionRole( ?string $controller, ?string $method, string $domain = SUB_DOMAIN ): PDOStatement {
        try {
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $sql = "SELECT role_id, restriction_type FROM access_restrictions WHERE domain=:domain";
            if( $controller === null || $controller === "" ) {
                $sql .= " AND controller IS NULL";
            } else {
                $sql .= " AND controller=:controller";
            }
            if( $method === null || $method === "" ) {
                $sql .= " AND method IS NULL";
            } else {
                $sql .= " AND method=:method";
            }
            $pdo->prepareQuery($sql);
            $pdo->bindParam("domain", $domain);
            if( $controller !== null && $controller !== "" ) {
                $pdo->bindParam("controller", $controller);
            }
            if( $method !== null && $controller !== "" ) {
                $pdo->bindParam("method", $method);
            }
            return $pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}
