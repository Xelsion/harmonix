<?php

namespace system\classes;

use PDOStatement;
use ReflectionException;
use JsonException;

use system\abstracts\AController;
use system\Core;
use system\exceptions\SystemException;

use models\AccessRestrictionTypeModel;
use models\ActorRoleModel;

class Auth {

    private ActorRoleModel $actor_role;
    private ActorRoleModel $restriction_role;
    private AccessRestrictionTypeModel $restriction_type;

    /**
     * The class constructor
     *
     * @throws JsonException
     * @throws SystemException
     * @throws ReflectionException
     */
    public function __construct() {
        $this->actor_role = Core::$_actor_role;
        $route = Core::$_router->getRoute(Core::$_request);
        $restriction = $this->getRestriction(get_class($route["controller"]), $route["method"]);
        $this->restriction_role = $restriction["role"];
        $this->restriction_type = $restriction["type"];
    }

    /**
     * Returns if the current actor has access to the current request
     * @return bool
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function hasAccess() : bool {
        return $this->getAccessibility($this->actor_role, $this->restriction_role, $this->restriction_type);
    }

    /**
     * Returns if the current actor has access to the given controller a method
     *
     * @param $controller
     * @param string $method
     * @param string|mixed $domain
     * @return bool
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function hasAccessTo( $controller, string $method, string $domain = SUB_DOMAIN) : bool {
        if( $controller instanceof AController ) {
            $controller = get_class($controller);
        }
        $actor_role = Core::$_actor->getRole($controller, $method, $domain);
        $restriction = $this->getRestriction($controller, $method, $domain);
        $restriction_role = $restriction["role"];
        $restriction_type = $restriction["type"];
        return $this->getAccessibility($actor_role, $restriction_role, $restriction_type);

    }

    /**
     * Returns if the actor role has access to the restriction role
     *
     * @param ActorRoleModel $actor_role
     * @param ActorRoleModel $restriction_role
     * @param AccessRestrictionTypeModel $restriction_type
     * @return bool
     *
     * @throws JsonException
     * @throws SystemException
     */
    private function getAccessibility( ActorRoleModel $actor_role, ActorRoleModel $restriction_role, AccessRestrictionTypeModel $restriction_type ) : bool {
        if( $restriction_role->id === 4 ) {
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
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
    private function getRestriction(?string $controller, ?string $method, string $domain = SUB_DOMAIN ) : array {
        $role_is_set = false;
        $result = array();

        $pdo_results = $this->getRestrictionRole( $controller, $method, $domain );
        if( $pdo_results->rowCount() === 1 ) {
            $row = $pdo_results->fetch();
            $result["role"] = new ActorRoleModel($row["role_id"]);
            $result["type"] = new AccessRestrictionTypeModel($row["restriction_type"]);
            $role_is_set = true;
        }

        if( !$role_is_set ) {
            $pdo_results = $this->getRestrictionRole( $controller, null, $domain );
            if( $pdo_results->rowCount() === 1 ) {
                $row = $pdo_results->fetch();
                $result["role"] = new ActorRoleModel($row["role_id"]);
                $result["type"] = new AccessRestrictionTypeModel($row["restriction_type"]);
                $role_is_set = true;
            }
        }

        if( !$role_is_set ) {
            $pdo_results = $this->getRestrictionRole( null, null, $domain );
            if( $pdo_results->rowCount() === 1 ) {
                $row = $pdo_results->fetch();
                $result["role"] = new ActorRoleModel($row["role_id"]);
                $result["type"] = new AccessRestrictionTypeModel($row["restriction_type"]);
                $role_is_set = true;
            }
        }

        if( !$role_is_set ) {
            $result["role"] = new ActorRoleModel(4);
            $result["type"] = new AccessRestrictionTypeModel(4);
        }

        return $result;
    }

    /**
     * Returns a SQL statement with the restriction role id and type id for the given controller and method
     *
     * @param string|null $controller
     * @param string|null $method
     * @param string|mixed $domain
     * @return PDOStatement
     *
     * @throws JsonException
     * @throws SystemException
     */
    private function getRestrictionRole( ?string $controller, ?string $method, string $domain = SUB_DOMAIN ): PDOStatement {
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $sql = "SELECT role_id, restriction_type FROM access_restrictions WHERE domain=:domain";
        if( $controller === null ) {
            $sql .= " AND controller IS NULL";
        } else {
            $sql .= " AND controller=:controller";
        }
        if( $method === null ) {
            $sql .= " AND method IS NULL";
        } else {
            $sql .= " AND method=:method";
        }
        $pdo->prepare($sql);
        $pdo->bindParam("domain", $domain);
        if( $controller !== null ) {
            $pdo->bindParam("controller", $controller);
        }
        if( $method !== null ) {
            $pdo->bindParam("method", $method);
        }
        return $pdo->execute();
    }

}
