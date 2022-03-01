<?php

namespace models;

use PDO;
use PDOException;
use RuntimeException;

use core\System;

class ActorRole extends entities\ActorRole {

	private static int $CAN_READ = 0b1000;
	private static int $CAN_CREATE = 0b0100;
	private static int $CAN_UPDATE = 0b0010;
	private static int $CAN_DELETE = 0b0001;

	public function getParentRole(): ?ActorRole {
		if( $this->child_of !== null ) {
			return new ActorRole($this->child_of);
		}
		return null;
	}

	public function getChildRoles(): array {
		$children = array();
		try {
			$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
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
			$current_role = $current_role->getParentRole();
		}
		return false;
	}

	public function isDescendantOf( ActorRole $role ): bool {
		$current_role = $this;
		while( $current_role->child_of !== null ) {
			if( $current_role->child_of === $role->id ) {
				return true;
			}
			$current_role = $current_role->getParentRole();
		}
		return false;
	}

	public function canReadAll(): bool {
		return ( $this->rights_all && self::$CAN_READ );
	}

	public function canCreateAll(): bool {
		return ( $this->rights_all && self::$CAN_CREATE );
	}

	public function canUpdateAll(): bool {
		return ( $this->rights_all && self::$CAN_UPDATE );
	}

	public function canDeleteAll(): bool {
		return ( $this->rights_all && self::$CAN_DELETE );
	}

	public function canReadGroup(): bool {
		return ( $this->rights_group && self::$CAN_READ );
	}

	public function canCreateGroup(): bool {
		return ( $this->rights_group && self::$CAN_CREATE );
	}

	public function canUpdateGroup(): bool {
		return ( $this->rights_group && self::$CAN_UPDATE );
	}

	public function canDeleteGroup(): bool {
		return ( $this->rights_group && self::$CAN_DELETE );
	}

	public function canReadOwn(): bool {
		return ( $this->rights_own && self::$CAN_READ );
	}

	public function canCreateOwn(): bool {
		return ( $this->rights_own && self::$CAN_CREATE );
	}

	public function canUpdateOwn(): bool {
		return ( $this->rights_own && self::$CAN_UPDATE );
	}

	public function canDeleteOwn(): bool {
		return ( $this->rights_own && self::$CAN_DELETE );
	}
}