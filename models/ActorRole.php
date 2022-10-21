<?php

namespace models;

use PDO;
use JsonException;

use system\Core;
use system\helper\SqlHelper;
use system\exceptions\SystemException;

/**
 * The Actor Role
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorRole extends entities\ActorRole {

	public static int $CAN_READ = 0b1000;
	public static int $CAN_CREATE = 0b0100;
	public static int $CAN_UPDATE = 0b0010;
	public static int $CAN_DELETE = 0b0001;

    /**
     * The class constructor
     * If id is 0 it will return an empty actor
     *
     * @param int $id
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function __construct( int $id = 0 ) {
		parent::__construct($id);
	}

    /**
     * Returns all actor roles that mach the given conditions,
     * The condition array is build like this:
     * <p>
     * array {
     *    array { col, condition, value },
     *    ...
     * }
     * </p>
     * All conditions are AND related
     *
     * @param array $conditions
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
    public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : array {
        $results = array();
        $pdo = Core::$_connection_manager->getConnection("mvc");
        if( !is_null($pdo) ) {
            $params = array();

            $query = "SELECT * FROM actor_roles";
            if( !empty($conditions) ) {
                $columns = array();

                foreach( $conditions as $i => $condition ) {
                    $columns[] = $condition[0] . $condition[1] . ":" . $i;
                    $params[$i] = $condition[2];
                }
                $query .= " WHERE " . implode(" AND ", $columns);
            }

            if( $order !== "" ) {
                $query .= " ORDER BY " . $order . " " . $direction;
            }

            if( $limit > 0 ) {
                $offset = $limit * ($page - 1);
                $query .= " LIMIT :limit OFFSET :offset";
                $params["limit"] = $limit;
                $params["offset"] = $offset;
            }

            $pdo->prepare($query);
            foreach( $params as $key => $value ) {
                $pdo->bindParam(":" . $key, $value, SqlHelper::getParamType($value));
            }
            $pdo->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
            $results = $pdo->execute()->fetchAll();
        }

        return $results;
    }

    /**
     * Returns the parent of this role or null if it has no parent.
     *
     * @return ActorRole|null
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function getParent(): ?ActorRole {
		if( $this->child_of !== null ) {
			return new ActorRole($this->child_of);
		}
		return null;
	}

    /**
     * Returns all children of this role in an array
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function getChildren(): array {
		$children = array();
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $pdo->prepare("SELECT * FROM actor_roles WHERE child_of=:id");
        $pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
        $results = $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
        foreach( $results as $child ) {
            $children[] = $child;
        }
		return $children;
	}

    /**
     * Checks if this role is the parent of the given role
     *
     * @param ActorRole $role
     * @return bool
     */
    public function isParentOf( ActorRole $role ): bool {
        return $this->id === $role->child_of;
    }

    /**
     * @param ActorRole $role
     *
     * @return bool
     */
    public function isGuest( ActorRole $role ): bool {
        return ( $role->id === 4 );
    }

    /**
     * Checks if this role is a sibling of the given role
     *
     * @param ActorRole $role
     * @return bool
     */
    public function isSiblingOf( ActorRole $role ): bool {
        return $this->child_of === $role->child_of;
    }

	/**
	 * Checks if this role is a child of the given role
	 *
	 * @param ActorRole $role
	 * @return bool
	 */
	public function isChildOf( ActorRole $role ): bool {
		return $this->child_of === $role->id;
	}

    /**
     * Checks if this role is an ancestor of the given role
     *
     * @param ActorRole $role
     * @return bool
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function isAncestorOf( ActorRole $role ): bool {
		$current_role = $role;
		while( $current_role->child_of !== null ) {
			if( $current_role->child_of === $this->id ) {
				return true;
			}
			$current_role = $current_role->getParent();
		}
		return false;
	}

    /**
     * Checks if this role is a descendant of the given role
     *
     * @param ActorRole $role
     * @return bool
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function isDescendantOf( ActorRole $role ): bool {
		$current_role = $this;
		while( $current_role->child_of !== null ) {
			if( $current_role->child_of === $role->id ) {
				return true;
			}
			$current_role = $current_role->getParent();
		}
		return false;
	}

	/**
	 * Checks if this role can read all content
	 *
	 * @return bool
	 */
	public function canReadAll(): bool {
		return ( $this->rights_all & self::$CAN_READ );
	}

	/**
	 * Checks if this role can create all content
	 *
	 * @return bool
	 */
	public function canCreateAll(): bool {
		return ( $this->rights_all & self::$CAN_CREATE );
	}

	/**
	 * Checks if this role can update all content
	 *
	 * @return bool
	 */
	public function canUpdateAll(): bool {
		return ( $this->rights_all & self::$CAN_UPDATE );
	}

	/**
	 * Checks if this role can delete all content
	 *
	 * @return bool
	 */
	public function canDeleteAll(): bool {
		return ( $this->rights_all & self::$CAN_DELETE );
	}

	/**
	 * Checks if this role can read own content and the content
	 * of all descendants
	 *
	 * @return bool
	 */
	public function canReadGroup(): bool {
		if( $this->canReadAll() ) {
			return true;
		}
		return ( $this->rights_group & self::$CAN_READ );
	}

	/**
	 * Checks if this role can create own content and content
	 * for of all descendants
	 *
	 * @return bool
	 */
	public function canCreateGroup(): bool {
		if( $this->canCreateAll() ) {
			return true;
		}
		return ( $this->rights_group & self::$CAN_CREATE );
	}

	/**
	 * Checks if this role can update own content and content
	 * for of all descendants
	 *
	 * @return bool
	 */
	public function canUpdateGroup(): bool {
		if( $this->canUpdateAll() ) {
			return true;
		}
		return ( $this->rights_group & self::$CAN_UPDATE );
	}

	/**
	 * Checks if this role can delete own content and content
	 * for of all descendants
	 *
	 * @return bool
	 */
	public function canDeleteGroup(): bool {
		if( $this->canDeleteAll() ) {
			return true;
		}
		return ( $this->rights_group & self::$CAN_DELETE );
	}

	/**
	 * Checks if this role can read own content
	 *
	 * @return bool
	 */
	public function canReadOwn(): bool {
		if( $this->canReadGroup() ) {
			return true;
		}
		return ( $this->rights_own & self::$CAN_READ );
	}

	/**
	 * Checks if this role can create own content
	 *
	 * @return bool
	 */
	public function canCreateOwn(): bool {
		if( $this->canCreateGroup() ) {
			return true;
		}
		return ( $this->rights_own & self::$CAN_CREATE );
	}

	/**
	 * Checks if this role can update own content
	 *
	 * @return bool
	 */
	public function canUpdateOwn(): bool {
		if( $this->canUpdateGroup() ) {
			return true;
		}
		return ( $this->rights_own & self::$CAN_UPDATE );
	}

	/**
	 * Checks if this role can delete own content
	 *
	 * @return bool
	 */
	public function canDeleteOwn(): bool {
		if( $this->canDeleteGroup() ) {
			return true;
		}
		return ( $this->rights_own & self::$CAN_DELETE );
	}

    /**
     * @param int $owner_id
     *
     * @return bool
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function canUpdate(int $owner_id): bool {
        $class = debug_backtrace()[1]['class'];
        $method = debug_backtrace()[1]['function'];
        $owner = new Actor($owner_id);
        $owner_role = $owner->getRole($class, $method, SUB_DOMAIN);
        if( $this->canUpdateAll() ) {
            return true;
        }
        if( (Core::$_actor_role->isAncestorOf($owner_role) || $this->isGuest($owner_role)) && $this->canUpdateGroup() ) {
            return true;
        }
        if( Core::$_actor->id === $owner_id && $this->canUpdateOwn() ) {
            return true;
        }
        return false;
    }

    /**
     * @param int $owner_id
     *
     * @return bool
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function canDelete(int $owner_id): bool {
        $class = debug_backtrace()[1]['class'];
        $method = debug_backtrace()[1]['function'];
        $owner = new Actor($owner_id);
        $owner_role = $owner->getRole($class, $method, SUB_DOMAIN);
        if( $this->canDeleteAll() ) {
            return true;
        }
        if( (Core::$_actor_role->isAncestorOf($owner_role) || $this->isGuest($owner_role) ) && $this->canDeleteGroup() ) {
            return true;
        }
        if( Core::$_actor->id === $owner_id && $this->canDeleteOwn() ) {
            return true;
        }
        return false;
    }

	/**
	 * Returns an array of all rights where the rights ar
	 * represented as a string.
	 *
	 * @return array|array[]
	 */
	public function getStringArray(): array {
		global $lang;
		$rights = array(
			"all"   => array(),
			"group" => array(),
			"own"   => array(),
		);
		if( $this->canCreateAll() ) {
			$rights["all"][] = Core::$_lang->getValue("right-chars", "create");
		}
		if( $this->canReadAll() ) {
			$rights["all"][] = Core::$_lang->getValue("right-chars", "read");
		}
		if( $this->canUpdateAll() ) {
			$rights["all"][] = Core::$_lang->getValue("right-chars", "update");
		}
		if( $this->canDeleteAll() ) {
			$rights["all"][] = Core::$_lang->getValue("right-chars", "delete");
		}
		if( $this->canCreateGroup() ) {
			$rights["group"][] = Core::$_lang->getValue("right-chars", "create");
		}
		if( $this->canReadGroup() ) {
			$rights["group"][] = Core::$_lang->getValue("right-chars", "read");
		}
		if( $this->canUpdateGroup() ) {
			$rights["group"][] = Core::$_lang->getValue("right-chars", "update");
		}
		if( $this->canDeleteGroup() ) {
			$rights["group"][] = Core::$_lang->getValue("right-chars", "delete");
		}
		if( $this->canCreateOwn() ) {
			$rights["own"][] = Core::$_lang->getValue("right-chars", "create");
		}
		if( $this->canReadOwn() ) {
			$rights["own"][] = Core::$_lang->getValue("right-chars", "read");
		}
		if( $this->canUpdateOwn() ) {
			$rights["own"][] = Core::$_lang->getValue("right-chars", "update");
		}
		if( $this->canDeleteOwn() ) {
			$rights["own"][] = Core::$_lang->getValue("right-chars", "delete");
		}
		return $rights;
	}
}
