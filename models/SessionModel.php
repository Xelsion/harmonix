<?php

namespace models;

use DateTime;
use Exception;
use JsonException;

use system\Core;
use system\helper\StringHelper;
use system\exceptions\SystemException;

/**
 * The SessionModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class SessionModel extends entities\Session {

	private string $_cookie_path = "/";
	private string $_cookie_domain = "";
	private int $_cookie_lifetime = 2;
	private bool $_cookie_secure = false;
	private string $_cookie_same_site = "lax";
	private string $_error;

    public function __construct() {
        parent::__construct();

        $configuration = Core::$_configuration->getSection("cookie");
        if( !empty($configuration) ) {
            $this->_cookie_domain = $configuration["domain"];
            $this->_cookie_lifetime = $configuration["lifetime"];
            $this->_cookie_secure = $configuration["secure"];
            $this->_cookie_same_site = $configuration["same_site"];
        }
    }

    /**
     * Starts the session
     *
     * @return ActorModel
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function start(): ActorModel {
        // do we have a login attempt?
		if( isset($_POST["login"], $_POST["email"], $_POST["password"]) ) {
			return $this->login($_POST["email"], $_POST["password"]);
		}

        // do we have a logout attempt?
		if( isset($_POST["logout"]) ) {
			$this->logout();
		}

        // do we have an active session?
		if( $this->actor_id > 0 ) {
            $date_time = new DateTime();
            $date_time->modify("+".$this->_cookie_lifetime." minutes");
            $this->ip = $_SERVER["REMOTE_ADDR"];
            $this->expired = $date_time->format("Y-m-d H:i:s");
            $this->update();
            $this->writeCookie();
			return new ActorModel($this->actor_id);
		}

		return new ActorModel();
	}

    /**
     * Try to log in with the given email and password
     *
     * @param string $email
     * @param string $password
     * @return ActorModel
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function login( string $email, string $password ): ActorModel {
        $permanent = ( array_key_exists("permanent_login", $_POST) && $_POST["permanent_login"] === "yes");
		$pdo = Core::$_connection_manager->getConnection("mvc");
		$pdo->prepare("SELECT * FROM actors WHERE email=:email AND deleted IS NULL");
		$pdo->bindParam(":email", $email);
		$stmt = $pdo->execute();
		if( $stmt->rowCount() === 1 ) {
			$actor = $stmt->fetchObject(ActorModel::class);
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
				$session_id = StringHelper::getGuID();
				$date_time = new DateTime();
                if( $permanent ) {
				    $date_time->modify("+".$this->_cookie_lifetime." minutes");
                } else {
                    $date_time->modify("+1 year");
                }
				$this->id = $session_id;
				$this->actor_id = $actor->id;
                $this->ip = $_SERVER["REMOTE_ADDR"];
				$this->expired = $date_time->format("Y-m-d H:i:s");
				$this->create();
                $this->writeCookie();
				return $actor;
			}
		} else {
            $this->_error = "E-Mail/Password is incorrect!";
        }
		return new ActorModel();
	}

    /**
     * Logout the current actor
     *
     * @return void
     *
     * @throws Exception
     * @throws JsonException
     * @throws SystemException
     */
	public function logout(): void {
		if( isset($_COOKIE["session"]) && $_COOKIE["session"] === $this->id ) {
            $date_time = new DateTime();
            $date_time->setTimestamp(time() - 3600);
			$this->delete();
			$this->actor_id = 0;
            $this->expired = $date_time->format('Y-m-d H:i:s');
            $this->writeCookie();
		}
	}

    /**
     * Rotates the session ID and updates the DB entry and the browser cookie
     *
     * @return void
     *
     * @throws SystemException
     */
    public function rotateSessionID(): void {
        $this->id = StringHelper::getGuID();
        print_debug("new session-id: ". $this->id);
        $this->update();
        $this->writeCookie();
    }

    /**
     * Writes the current session into the browser cookies
     *
     * @return void
     */
    private function writeCookie() {
        $date_time = DateTime::createFromFormat("Y-m-d H:i:s", $this->expired);
        $cookie_options = array(
            'expires' => $date_time->getTimestamp(),
            'path'    => $this->_cookie_path,
            'domain'  => $this->_cookie_domain,
            'secure'  => $this->_cookie_secure,
            'samesite' => $this->_cookie_same_site
        );
        setcookie("session", $this->id, $cookie_options);
    }

    /**
     * Returns the current error message
     *
     * @return string
     */
	public function getError(): string {
		return $this->_error;
	}

}
