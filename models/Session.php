<?php

namespace models;

use PDO;
use DateTime;
use Exception;
use RuntimeException;

use system\Core;

/**
 * The Session
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Session extends entities\Session {

	private string $_cookie_path = "/";
	private string $_cookie_domain = "";
	private int $_cookie_lifetime = 2;
	private bool $_cookie_secure = false;
	private string $_error;

    /**
     * @return int
     * @throws \Exception
     */
    public static function getLastModification() : int {
        $created = 0;
        $updated = 0;
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $pdo->prepare("SELECT max(created) as created, max(updated) as updated FROM sessions LIMIT 1");
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
     * Starts the session
     *
     * @return Actor
     */
	public function start(): Actor {
		$configuration = Core::$_configuration->getSection("cookie");
		$this->_cookie_domain = $configuration["domain"];
		$this->_cookie_lifetime = $configuration["lifetime"];
		$this->_cookie_secure = $configuration["secure"];

        // do we have a login attempt?
		if( isset($_POST["login"], $_POST["email"], $_POST["password"]) ) {
			try {
				return $this->login($_POST["email"], $_POST["password"]);
			} catch( Exception $e ) {
				throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
			}
		}

        // do we have a logout attempt?
		if( isset($_POST["logout"]) ) {
			$this->logout();
		}

        // do we have an active session?
		if( $this->actor_id > 0 ) {
            $date_time = new DateTime();
            $date_time->modify("+".$this->_cookie_lifetime." hour");
            $this->ip = $_SERVER["REMOTE_ADDR"];
            $this->expired = $date_time->format("Y-m-d H:i:s");
            $this->update();

            $cookie_options = array(
                'expires' => $date_time->getTimestamp(),
                'path'    => $this->_cookie_path,
                'domain'  => $this->_cookie_domain,
                'secure'  => $this->_cookie_secure
            );
            setcookie("session", $this->id, $cookie_options);
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
		$pdo->prepare("SELECT * FROM actors WHERE email=:email AND deleted IS NULL");
		$pdo->bindParam(":email", $email, PDO::PARAM_STR);
		$stmt = $pdo->execute();
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
				$this->_error = "E-Mail/Password is incorrect!";
			} else {
				$session_id = MD5(time());
				$date_time = new DateTime();
				$date_time->modify("+".$this->_cookie_lifetime." hour");
				$this->id = $session_id;
				$this->actor_id = $actor->id;
                $this->ip = $_SERVER["REMOTE_ADDR"];
				$this->expired = $date_time->format("Y-m-d H:i:s");
				$this->create();

				$cookie_options = array(
					'expires' => $date_time->getTimestamp(),
					'path'    => $this->_cookie_path,
					'domain'  => $this->_cookie_domain,
					'secure'  => $this->_cookie_secure
				);
				setcookie("session", $session_id, $cookie_options);
				return $actor;
			}
		} else {
            $this->_error = "E-Mail/Password is incorrect!";
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
			$cookie_options = array(
				'expires' => time() - 3600,
				'path'    => $this->_cookie_path,
				'domain'  => $this->_cookie_domain,
				'secure'  => $this->_cookie_secure
			);
			setcookie("session", "", $cookie_options);
		}
	}

	public function getError(): string {
		return $this->_error;
	}

}