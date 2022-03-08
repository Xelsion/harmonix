<?php

namespace models;

use PDO;
use DateTime;
use Exception;
use RuntimeException;

use core\Core;

/**
 * The Session
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Session extends entities\Session {

	private int $_lifetime = 2;
	private string $_error;

	public function start(): Actor {
		if( isset($_POST["login"], $_POST["email"], $_POST["password"]) ) {
			try {
				return $this->login($_POST["email"], $_POST["password"]);
			} catch( Exception $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}

		if( isset($_POST["logout"]) ) {
			$this->logout();
		}

		if( $this->actor_id > 0 ) {
			return new Actor($this->actor_id);
		}

		return new Actor();
	}

	/**
	 * Try to log in with the given email and password
	 *
	 * @param string $email
	 * @param string $password
	 * @return Actor
	 * @throws Exception
	 */
	public function login( string $email, string $password ): Actor {
		$pdo = Core::$_connection_manager->getConnection("mvc");
		$stmt = $pdo->prepare("SELECT * FROM actors WHERE email=:email");
		$stmt->bindParam(":email", $email, PDO::PARAM_STR);
		$stmt->execute();
		if( $stmt->rowCount() === 1 ) {
			$actor = $stmt->fetchObject(Actor::class);
			if( $actor->login_disabled ) {
				$this->_error = "Account is disabled!";
			} else if( $actor->login_fails >= 3 ) {
				$actor->login_fails = 0;
				$actor->login_disabled = true;
				$actor->update();
				$this->_error = "To many login fails!";
			} else if( !password_verify($password, $actor->password) ) {
				$actor->login_fails++;
				$actor->update();
				$this->_error = "EMail/Password is incorrect!";
			} else {
				$session_id = MD5(time());
				$date_time = new DateTime();
				$date_time->modify("+".$this->_lifetime." hour");
				$this->id = $session_id;
				$this->actor_id = $actor->id;
				$this->expired = $date_time->format("Y-m-d H:i:s");
				$this->create();
				setcookie("session", $session_id, $date_time->getTimestamp());
				return $actor;
			}
		} else {
            $this->_error = "EMail/Password is incorrect!";
        }
		return new Actor();
	}

	/**
	 * Log out the current actor
	 */
	public function logout(): void {
		if( isset($_COOKIE["session"]) && $_COOKIE["session"] === $this->id ) {
			$this->delete();
			$this->actor_id = 0;
			setcookie("session", "", time() - 1);
		}
	}

	public function getError(): string {
		return $this->_error;
	}
}