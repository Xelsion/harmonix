<?php

namespace system\classes;

use models\AccessRestrictionType;
use models\ActorRole;
use PDOStatement;
use system\abstracts\AController;
use system\Core;

class Auth {

    private ActorRole $_actor_role;
    private ActorRole $_restriction_role;
    private AccessRestrictionType $_restriction_type;

    /**
     * The class constructor
     */
    public function __construct() {
        $this->_actor_role = Core::$_actor_role;
        $route = Core::$_router->getRoute(Core::$_request);
        $restriction = $this->getRestriction(get_class($route["controller"]), $route["method"]);
        $this->_restriction_role = $restriction["role"];
        $this->_restriction_type = $restriction["type"];
    }

    /**
     * Returns if the currend actor has access to the current request
     * @return bool
     */
    public function hasAccess() : bool {
        return $this->getAccessibility($this->_actor_role, $this->_restriction_role, $this->_restriction_type);
    }

    /**
     * Returns if the current actor has access to the given controller an method
     *
     * @param AController $controller
     * @param string $method
     * @return bool
     */
    public function hasAccessTo( AController $controller, string $method) : bool {
        $actor_role = Core::$_actor->getRole(get_class($controller), $method);
        $restriction = $this->getRestriction(get_class($controller), $method);
        $restriction_role = $restriction["role"];
        $restriction_type = $restriction["type"];
        return $this->getAccessibility($actor_role, $restriction_role, $restriction_type);

    }

    /**
     * Returns if the actor role has access to the restriction role
     *
     * @param ActorRole $actor_role
     * @param ActorRole $restriction_role
     * @param AccessRestrictionType $restriction_type
     * @return bool
     */
    private function getAccessibility( ActorRole $actor_role, ActorRole $restriction_role, AccessRestrictionType $restriction_type ) : bool {
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
     * @return array
     */
    private function getRestriction(?string $controller, ?string $method) : array {
        $role_is_set = false;
        $result = array();

        $pdo_results = $this->getRestrictionRole( $controller, $method );
        if( $pdo_results->rowCount() === 1 ) {
            $row = $pdo_results->fetch();
            $result["role"] =  new ActorRole($row["role_id"]);
            $result["type"] = new AccessRestrictionType($row["restriction_type"]);
            $role_is_set = true;
        }

        if( !$role_is_set ) {
            $pdo_results = $this->getRestrictionRole( $controller, null );
            if( $pdo_results->rowCount() === 1 ) {
                $row = $pdo_results->fetch();
                $result["role"] =  new ActorRole($row["role_id"]);
                $result["type"] = new AccessRestrictionType($row["restriction_type"]);
                $role_is_set = true;
            }
        }

        if( !$role_is_set ) {
            $pdo_results = $this->getRestrictionRole( null, null );
            if( $pdo_results->rowCount() === 1 ) {
                $row = $pdo_results->fetch();
                $result["role"] =  new ActorRole($row["role_id"]);
                $result["type"] = new AccessRestrictionType($row["restriction_type"]);
                $role_is_set = true;
            }
        }

        if( !$role_is_set ) {
            $result["role"] =  new ActorRole(4);
            $result["type"] = new AccessRestrictionType(4);
        }

        return $result;
    }

    /**
     * Returns a SQL statement with the restriction role id and type id for the given controller and method
     *
     * @param string|null $controller
     * @param string|null $method
     * @return PDOStatement
     */
    private function getRestrictionRole( ?string $controller, ?string $method ): PDOStatement {
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
        $pdo->bindParam("domain", SUB_DOMAIN);
        if( $controller !== null ) {
            $pdo->bindParam("controller", $controller);
        }
        if( $method !== null ) {
            $pdo->bindParam("method", $method);
        }
        return $pdo->execute();
    }

}