<?php

namespace models;

use DateTime;
use Exception;
use JsonException;
use lib\core\System;
use lib\exceptions\SystemException;
use lib\helper\StringHelper;
use models\entities\Session;

/**
 * The SessionModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class SessionModel extends Session {

    public bool $encryption = false;

    public string $cookie_name = "session";
    public string $cookie_path = "/";
    public string $cookie_domain = "";
	private int $cookie_lifetime = 2;
	private bool $cookie_secure = false;
	private string $cookie_same_site = "lax";
	private string $error;

    /**
     * The class constructor
     * Initializes the cookie settings from the configuration
     *
     */
    public function __construct() {
        parent::__construct();

        $configuration = System::$Core->configuration->getSection("cookie");
        if( !empty($configuration) ) {
            $this->cookie_name = $configuration["name"];
            $this->cookie_domain = $configuration["domain"];
            $this->cookie_lifetime = $configuration["lifetime"];
            $this->cookie_secure = $configuration["secure"];
            $this->cookie_same_site = $configuration["same_site"];
        }
        $this->encryption = (bool)System::$Core->configuration->getSectionValue("security", "encrypted_session");
    }

    /**
     * Starts the session
     *
     * @return ActorModel
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function getActor(): ActorModel {
        $actor = new ActorModel();
        // do we have an actor?
		if( $this->actor_id > 0 ) {
            $date_time = new DateTime();
            $date_time->modify("+".$this->cookie_lifetime." minutes");
            $this->ip = $_SERVER["REMOTE_ADDR"];
            $this->expired = $date_time->format("Y-m-d H:i:s");
            $actor = new ActorModel($this->actor_id);

            $this->update();
            $this->writeCookie();
		}
        return $actor;
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
	public function login( string $email, string $password ): bool {
        $permanent = ( array_key_exists("permanent_login", $_POST) && $_POST["permanent_login"] === "yes");
		$pdo = System::$Core->connection_manager->getConnection("mvc");
		$pdo->prepareQuery("SELECT id, password FROM actors WHERE email=:email AND deleted IS NULL AND login_disabled=0");
		$pdo->bindParam(":email", $email);
		$stmt = $pdo->execute();
		if( $stmt->rowCount() === 1 ) {
			$result = $stmt->fetch();
			if( password_verify($password, $result["password"]) ) {
				$session_id = StringHelper::getGuID();
				$date_time = new DateTime();
                if( $permanent ) {
				    $date_time->modify("+".$this->cookie_lifetime." minutes");
                } else {
                    $date_time->modify("+1 year");
                }
				$this->id = $session_id;
				$this->actor_id = (int)$result["id"];
                $this->ip = $_SERVER["REMOTE_ADDR"];
				$this->expired = $date_time->format("Y-m-d H:i:s");
				$this->create();
                $this->writeCookie();
				return true;
			}
		}
        $this->error = "E-Mail/Password is incorrect!";
		return false;
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
        redirect("/");
	}

    /**
     * Writes the current session into the browser cookies
     *
     * @return void
     */
    public function writeCookie(): void {
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
