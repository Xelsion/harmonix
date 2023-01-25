<?php
namespace lib\classes;

use PDOStatement;
use lib\App;
use lib\core\Request;
use lib\core\Router;
use lib\abstracts\AController;
use lib\manager\ConnectionManager;
use models\AccessRestrictionTypeModel;
use models\ActorRoleModel;

use Exception;
use lib\exceptions\SystemException;

/**
 * The Auth class
 * checks if the current actor role has access to the given route
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Auth {

    private ActorRoleModel $actor_role;
    private ActorRoleModel $restriction_role;
    private AccessRestrictionTypeModel $restriction_type;

    /**
     * The class constructor
     *
     * @throws SystemException
     */
    public function __construct( protected Request $request ) {
        $this->actor_role = App::$curr_actor_role;
        $route = App::getInstance(Router::class)->getRoute($request);
        $restriction = $this->getRestriction(get_class($route["controller"]), $route["method"]);
        $this->restriction_role = $restriction["role"];
        $this->restriction_type = $restriction["type"];
    }

    /**
     * Returns if the current actor has access to the current request
     *
     * @return bool
     *
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
     * Returns if the actor role has access to the restriction role
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
            $result["role"] = App::getInstance(ActorRoleModel::class, null, ["id" => $row["role_id"]]);
            $result["type"] = App::getInstance(AccessRestrictionTypeModel::class, null, ["id" => $row["restriction_type"]]);
            return $result;
        }

        $pdo_results = $this->getRestrictionRole( $controller, null, $domain );
        if( $pdo_results->rowCount() === 1 ) {
            $row = $pdo_results->fetch();
            $result["role"] = App::getInstance(ActorRoleModel::class, null, ["id" => $row["role_id"]]);
            $result["type"] = App::getInstance(AccessRestrictionTypeModel::class, null, ["id" => $row["restriction_type"]]);
            return $result;
        }

        $pdo_results = $this->getRestrictionRole( null, null, $domain );
        if( $pdo_results->rowCount() === 1 ) {
            $row = $pdo_results->fetch();
            $result["role"] = App::getInstance(ActorRoleModel::class, null, ["id" => $row["role_id"]]);
            $result["type"] = App::getInstance(AccessRestrictionTypeModel::class, null, ["id" => $row["restriction_type"]]);
            return $result;
        }

        $result["role"] = App::getInstance(ActorRoleModel::class, null, ["id" => 4]);
        $result["type"] = App::getInstance(AccessRestrictionTypeModel::class, null, ["id" => 4]);
        return $result;
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
            $cm = App::getInstance(ConnectionManager::class);
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
