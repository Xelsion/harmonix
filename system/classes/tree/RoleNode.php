<?php

namespace system\classes\tree;

use models\ActorRoleModel;

class RoleNode extends TreeNode {

    // the target url of this menu item
    public ?ActorRoleModel $_role = null;

    /**
     * The constructor creates a MenuItem
     *
     * @param ActorRoleModel $role
     */
    public function __construct( ActorRoleModel $role ) {
        parent::__construct($role->id, $role->child_of, $role->name);
        $this->_role = $role;
    }

}
