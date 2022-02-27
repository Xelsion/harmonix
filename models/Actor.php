<?php

namespace models;

use core\helper\StringHelper;
use core\System;
use DateTime;
use PDO;
use PDOException;
use RuntimeException;

class Actor {

	public int $id = 0;
	public string $email;
	public string $password;
	public string $first_name;
	public string $last_name;
	public int $login_fails;
	public bool $login_disabled;
	public string $created;
	public ?string $updated;
	public ?string $deleted;

	public function __construct( int $id = 0 ) {
		if( $id > 0 ) {
			$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT * FROM actors WHERE id=:id");
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
			$stmt->setFetchMode(PDO::FETCH_INTO, $this);
			$stmt->execute();
			$stmt->fetch();
		}
	}

	public function save(): void {
		$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
		try {
			if( $this->id === 0 ) {
				$sql = "INSERT INTO actors (email, password, first_name, last_name) VALUES (:email, :password, :first_name, :last_name)";
				$this->password = StringHelper::getBCrypt($this->password);
				$stmt = $pdo->prepare($sql);
			} else {
				$sql = "UPDATE actors SET email=:email, password=:password, first_name=:first_name, last_name=:last_name WHERE id=:id";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
			}
			$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
			$stmt->bindParam(':password', $this->password, PDO::PARAM_STR);
			$stmt->bindParam(':first_name', $this->first_name, PDO::PARAM_STR);
			$stmt->bindParam(':last_name', $this->last_name, PDO::PARAM_STR);
			$stmt->execute();
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
	}

	public function delete(): bool {
		if( $this->id > 0 ) {
			$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
			try {
				$stmt = $pdo->prepare("DELETE FROM actors WHERE id=:id");
				$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
				$stmt->setFetchMode(PDO::FETCH_INTO, $this);
				$stmt->execute();
				return true;
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}
		return false;
	}
}