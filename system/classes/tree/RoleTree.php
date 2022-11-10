<?php

namespace system\classes\tree;

use Exception;

use models\ActorRoleModel;

class RoleTree extends TreeWalker {

	private static ?RoleTree $instance = null;

    /**
     * The class constructor
     *
     * Collect all actor roles and add then to the tree
     *
     * @throws Exception
     */
	private function __construct() {
		parent::__construct();
		$actor_roles = ActorRoleModel::find();
		foreach( $actor_roles as $role ) {
            $node = new RoleNode($role);
			$this->addNode($node);
		}
	}

    /**
     * The initializer for this class
     *
     * @return RoleTree
     */
	public static function getInstance(): RoleTree {
		if( static::$instance === null ) {
			static::$instance = new RoleTree();
		}
		return static::$instance;
	}

}
