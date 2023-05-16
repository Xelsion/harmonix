<?php
namespace lib\core\classes;

use lib\App;
use lib\core\blueprints\AController;
use lib\core\enums\ActorRole;
use lib\core\exceptions\SystemException;
use lib\core\Request;
use lib\core\Router;
use models\AccessRestrictionTypeModel;
use models\ActorRoleModel;
use repositories\AccessRestrictionRepository;

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
    private array $restriction_setup = array();

    /**
     * The class constructor
     *
     * @throws \lib\core\exceptions\SystemException
     */
    public function __construct( protected readonly Request $request ) {
        $this->actor_role = App::$curr_actor_role;
        $this->initRestrictions();
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
     * @param string|AController $controller
     * @param string $method
     * @param string|mixed $domain
     *
     * @return bool
     *
     * @throws SystemException
     */
    public function hasAccessTo( string|AController $controller, string $method, string $domain = SUB_DOMAIN) : bool {
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
        $controller = $controller ?? "null";
        $method = $method ?? "null";

        $role_id = ActorRole::Guest->value;
        $restriction_type = 1;
        if( isset($this->restriction_setup[$domain][$controller][$method])) {
            $entry = $this->restriction_setup[$domain][$controller][$method];
            $role_id = $entry["role_id"];
            $restriction_type = $entry["restriction_type"];
        } elseif( isset($this->restriction_setup[$domain][$controller]["null"])) {
            $entry = $this->restriction_setup[$domain][$controller]["null"];
            $role_id = $entry["role_id"];
            $restriction_type = $entry["restriction_type"];
        } else if( isset($this->restriction_setup[$domain]["null"]["null"])) {
            $entry = $this->restriction_setup[$domain]["null"]["null"];
            $role_id = $entry["role_id"];
            $restriction_type = $entry["restriction_type"];
        }

        return [
            "role" => App::getInstanceOf(ActorRoleModel::class, null, ["id" => $role_id]),
            "type" => App::getInstanceOf(AccessRestrictionTypeModel::class, null, ["id" => $restriction_type])
        ];
    }

    /**
     * @return void
     * @throws SystemException
     */
    private function initRestrictions(): void {
        $repository = App::getInstanceOf(AccessRestrictionRepository::class);
        $restrictions = $repository->getAll();
        foreach ($restrictions as $entry) {
            $controller = $entry->controller ?? "null";
            $method = $entry->method ?? "null";
            $this->restriction_setup[$entry->domain][$controller][$method] = [
                "role_id" => $entry->role_id,
                "restriction_type" => $entry->restriction_type
            ];
        }
    }
}
