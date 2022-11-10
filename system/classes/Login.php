<?php

namespace system\classes;

use DateTime;
use Exception;
use JsonException;

use models\ActorModel;
use models\entities\Session;
use system\Core;
use system\helper\StringHelper;
use system\exceptions\SystemException;

/**
 * The Login
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Login extends Session {

    private bool $encryption = false;

    private string $cookie_name = "session";
	private string $cookie_path = "/";
	private string $cookie_domain = "";
	private int $cookie_lifetime = 2;
	private bool $cookie_secure = false;
	private string $cookie_same_site = "lax";
	private string $error;

    public function __construct() {
        parent::__construct();

        $configuration = Core::$_configuration->getSection("cookie");
        if( !empty($configuration) ) {
            $this->cookie_name = $configuration["name"];
            $this->cookie_domain = $configuration["domain"];
            $this->cookie_lifetime = $configuration["lifetime"];
            $this->cookie_secure = $configuration["secure"];
            $this->cookie_same_site = $configuration["same_site"];
        }

        $this->encryption = (bool)Core::$_configuration->getSectionValue("security", "encrypted_session");
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
        if( isset($_COOKIE[$this->cookie_name]) ) {
            $session_id = ($this->encryption)
                ? StringHelper::decrypt($_COOKIE[$this->cookie_name])
                : $_COOKIE[$this->cookie_name];
            $this->init($session_id);
        }

        // do we have a login attempt?
		if( isset($_POST["login"], $_POST["email"], $_POST["password"]) ) {
			return $this->login($_POST["email"], $_POST["password"]);
		}

        // do we have a logout attempt?
		if( isset($_POST["logout"]) ) {
			$this->logout();
            return new ActorModel();
		}

        // do we have an active session?
		if( $this->actor_id > 0 ) {
            $date_time = new DateTime();
            $date_time->modify("+".$this->cookie_lifetime." minutes");
            $this->ip = $_SERVER["REMOTE_ADDR"];
            $this->expired = $date_time->format("Y-m-d H:i:s");
            $actor = new ActorModel($this->actor_id);
            if( $actor->id === 1 && ($this->as_actor > 0 || isset($_POST["login-as"], $_POST["actor_id"])) ) {
                if( isset($_POST["actor_id"]) ) {
                    $this->as_actor = (int)$_POST["actor_id"];
                }
                $actor = new ActorModel($this->as_actor);
            }
            $this->update();
            $this->writeCookie();
			return $actor;
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
				$this->error = "Account is disabled!";
			} else if( $actor->login_fails >= 3 ) {
				$actor->login_fails = 0;
				$actor->login_disabled = true;
				$actor->update();
				$this->error = "To many login fails!";
			} else if( !password_verify($password, $actor->password) ) {
				$actor->login_fails++;
				$actor->update();
				$this->error = "E-Mail/Password is incorrect!";
			} else {
				$session_id = StringHelper::getGuID();
				$date_time = new DateTime();
                if( $permanent ) {
				    $date_time->modify("+".$this->cookie_lifetime." minutes");
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
            $this->error = "E-Mail/Password is incorrect!";
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
        if( $this->as_actor > 0 ) {
            $this->as_actor = 0;
            $this->update();
            $this->writeCookie();
        } else {
            $session_id = ($this->encryption)
                ? StringHelper::decrypt($_COOKIE[$this->cookie_name])
                : $_COOKIE[$this->cookie_name];
            if( isset($_COOKIE[$this->cookie_name]) && $session_id === $this->id ) {
                $date_time = new DateTime();
                $date_time->setTimestamp(time() - 3600);
                $this->delete();
                $this->actor_id = 0;
                $this->expired = $date_time->format('Y-m-d H:i:s');
                $this->writeCookie();
            }
        }
	}

    /**
     * Writes the current session into the browser cookies
     *
     * @return void
     */
    private function writeCookie(): void {
        $date_time = DateTime::createFromFormat("Y-m-d H:i:s", $this->expired);
        $session_id = ($this->encryption)
            ? StringHelper::encrypt($this->id)
            : $this->id;
        $cookie_options = array(
            'expires' => $date_time->getTimestamp(),
            'path'    => $this->cookie_path,
            'domain'  => $this->cookie_domain,
            'secure'  => $this->cookie_secure,
            'samesite' => $this->cookie_same_site
        );
        setcookie($this->cookie_name, $session_id, $cookie_options);
    }

    /**
     * Returns the current error message
     *
     * @return string
     */
	public function getError(): string {
		return $this->error;
	}

}
