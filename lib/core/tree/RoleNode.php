<?php
namespace lib\core\tree;

use models\ActorRoleModel;

/**
 * The RoleNode class extends TreeNode
 * Represents a Node within a RoleTree
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class RoleNode extends TreeNode {

    // the target url of this menu item
    public ?ActorRoleModel $role = null;

    /**
     * The constructor creates a MenuItem
     *
     * @param ActorRoleModel $role
     */
    public function __construct( ActorRoleModel $role ) {
        parent::__construct($role->id, $role->child_of, $role->name);
        $this->role = $role;
    }

}
