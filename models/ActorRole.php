<?php

namespace models;

use PDO;
use PDOException;
use RuntimeException;

use core\Core;

class ActorRole extends entities\ActorRole {

	public static int $CAN_READ = 0b1000;
	public static int $CAN_CREATE = 0b0100;
	public static int $CAN_UPDATE = 0b0010;
	public static int $CAN_DELETE = 0b0001;

	public function __construct( int $id = 0 ) {
		parent::__construct($id);
	}

	public static function find( array $conditions ) {
		if( empty($conditions) ) {
			return static::findAll();
		}

		$columns = array();
		foreach( $conditions as $condition ) {
			$columns[] = $condition[0].$condition[1].":".$condition[0];
		}

		$pdo = Core::$_connection_manager->getConnection("mvc");
		$sql = "SELECT * FROM actor_roles WHERE ".implode(" AND ", $columns);
		$stmt = $pdo->prepare($sql);
		foreach( $conditions as $condition ) {
			$stmt->bindParam(":".$condition[0], $condition[2], static::getParamType($condition[2]));
		}
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

	public static function findAll() {
		$index = 0;
		$limit = 20;
		$pdo = Core::$_connection_manager->getConnection("mvc");
		$stmt = $pdo->prepare("SELECT * FROM actor_roles LIMIT :index, :max");
		$stmt->bindParam("index", $index, PDO::PARAM_INT);
		$stmt->bindParam("max", $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

	public function getParent(): ?ActorRole {
		if( $this->child_of !== null ) {
			return new ActorRole($this->child_of);
		}
		return null;
	}

	public function getChildren(): array {
		$children = array();
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT * FROM actor_roles WHERE child_of=:id");
			$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
			$stmt->setFetchMode(PDO::FETCH_OBJ, __CLASS__);
			$stmt->execute();
			while( $child = $stmt->fetch() ) {
				$children[] = $child;
			}
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
		return $children;
	}

	public function isChildOf( ActorRole $role ): bool {
		return $this->child_of === $role->id;
	}

	public function isParentOf( ActorRole $role ): bool {
		return $this->id === $role->child_of;
	}

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

	public function canReadAll(): bool {
		return ( $this->rights_all & self::$CAN_READ );
	}

	public function canCreateAll(): bool {
		return ( $this->rights_all & self::$CAN_CREATE );
	}

	public function canUpdateAll(): bool {
		return ( $this->rights_all & self::$CAN_UPDATE );
	}

	public function canDeleteAll(): bool {
		return ( $this->rights_all & self::$CAN_DELETE );
	}

	public function canReadGroup(): bool {
		return ( $this->rights_group & self::$CAN_READ );
	}

	public function canCreateGroup(): bool {
		return ( $this->rights_group & self::$CAN_CREATE );
	}

	public function canUpdateGroup(): bool {
		return ( $this->rights_group & self::$CAN_UPDATE );
	}

	public function canDeleteGroup(): bool {
		return ( $this->rights_group & self::$CAN_DELETE );
	}

	public function canReadOwn(): bool {
		return ( $this->rights_own & self::$CAN_READ );
	}

	public function canCreateOwn(): bool {
		return ( $this->rights_own & self::$CAN_CREATE );
	}

	public function canUpdateOwn(): bool {
		return ( $this->rights_own & self::$CAN_UPDATE );
	}

	public function canDeleteOwn(): bool {
		return ( $this->rights_own & self::$CAN_DELETE );
	}

	public function getStringArray() {
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