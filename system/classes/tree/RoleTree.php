<?php

namespace system\classes\tree;

use Exception;
use models\ActorRole;

class RoleTree extends TreeWalker {

	private static ?RoleTree $_instance = null;

    /**
     * The class constructor
     *
     * Collect all actor roles and add then to the tree
     *
     * @throws Exception
     */
	private function __construct() {
		parent::__construct();
		$actor_roles = ActorRole::findAll();
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
		if( static::$_instance === null ) {
			static::$_instance = new RoleTree();
		}
		return static::$_instance;
	}

}