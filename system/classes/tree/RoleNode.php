<?php

namespace system\classes\tree;

use models\ActorRole;

class RoleNode extends TreeNode {

    // the target url of this menu item
    public ?ActorRole $_role = null;

    /**
     * The constructor creates a MenuItem
     *
     * @param ActorRole $role
     */
    public function __construct( ActorRole $role ) {
        parent::__construct($role->id, $role->child_of, $role->name);
        $this->_role = $role;
    }

}