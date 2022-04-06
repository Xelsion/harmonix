<?php

namespace models;

use DateTime;
use PDO;
use PDOException;
use RuntimeException;

use system\Core;
use system\helper\SqlHelper;

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
     * @return array|false|null
     */
    public static function find( array $conditions, ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : ?array {
        $pdo = SqlHelper::findIn("mvc", "actor_roles", $conditions, $order, $direction, $limit, $page);
        return $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    /**
     * Returns all actor roles
     * If limit is greater than 0 the query will return
     * that many results starting at index.
     * Returns false if an error occurs
     *
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return array|false
     */
    public static function findAll( ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): ?array {
        $pdo = SqlHelper::findAllIn("mvc", "actor_roles", $order, $direction, $limit, $page);
        return $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public static function getLastModification() : int {
        $created = 0;
        $updated = 0;
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $pdo->prepare("SELECT max(created) as created, max(updated) as updated FROM actor_roles LIMIT 1");
        $row = $pdo->execute()->fetch();
        if( $row ) {
            $created = new DateTime($row["created"]);
            $created = $created->getTimestamp();
            $updated = new DateTime($row["updated"]);
            $updated = $updated->getTimestamp();
        }
        return ( $created >= $updated ) ? $created : $updated;
    }

	/**
	 * Returns the parent of this role or null if it has no parent.
	 *
	 * @return ActorRole|null
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
	 */
	public function getChildren(): array {
		$children = array();
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$pdo->prepare("SELECT * FROM actor_roles WHERE child_of=:id");
			$pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
			$results = $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
			foreach( $results as $child ) {
				$children[] = $child;
			}
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
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
			$rights["all"][] = $lang['rights_char']["create"];
		}
		if( $this->canReadAll() ) {
			$rights["all"][] = $lang['rights_char']["read"];
		}
		if( $this->canUpdateAll() ) {
			$rights["all"][] = $lang['rights_char']["update"];
		}
		if( $this->canDeleteAll() ) {
			$rights["all"][] = $lang['rights_char']["delete"];
		}
		if( $this->canCreateGroup() ) {
			$rights["group"][] = $lang['rights_char']["create"];
		}
		if( $this->canReadGroup() ) {
			$rights["group"][] = $lang['rights_char']["read"];
		}
		if( $this->canUpdateGroup() ) {
			$rights["group"][] = $lang['rights_char']["update"];
		}
		if( $this->canDeleteGroup() ) {
			$rights["group"][] = $lang['rights_char']["delete"];
		}
		if( $this->canCreateOwn() ) {
			$rights["own"][] = $lang['rights_char']["create"];
		}
		if( $this->canReadOwn() ) {
			$rights["own"][] = $lang['rights_char']["read"];
		}
		if( $this->canUpdateOwn() ) {
			$rights["own"][] = $lang['rights_char']["update"];
		}
		if( $this->canDeleteOwn() ) {
			$rights["own"][] = $lang['rights_char']["delete"];
		}
		return $rights;
	}
}