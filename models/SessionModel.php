<?php

namespace models;

use DateTime;
use Exception;
use lib\App;
use lib\core\classes\Configuration;
use lib\core\exceptions\SystemException;
use lib\helper\StringHelper;
use models\entities\Session;
use repositories\ActorRepository;
use repositories\SessionRepository;

/**
 * The SessionModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class SessionModel extends Session {

	public bool $_rotate_session = false;

	public bool $use_cookies = false;

	// Defines if the cookie will be encrypted or not
	public bool $encryption = false;

	// The name of the cookie
	public string $cookie_name = "session";

	// The cookie path
	public string $cookie_path = "/";

	// The cookie domain
	public string $cookie_domain;

	// The cookie lifetime in hours
	private int $cookie_lifetime = 2;

	// Sets if the cookies secure type
	private bool $cookie_secure = false;

	// Defines the cookie site setting
	private string $cookie_same_site = "lax";

	// Stores any errors
	private string $error;

	/**
	 * The class constructor
	 * Initializes the cookie settings from the configuration
	 *
	 * @param Configuration $config
	 * @throws SystemException
	 */
	public function __construct(Configuration $config) {
		$cookie_settings = $config->getSection("session");
		$rotate_session = $config->getSectionValue("security", "rotate_session");
		$this->use_cookies = (bool)$config->getSectionValue("session", "use_cookies");
		if( !is_null($rotate_session) ) {
			$this->_rotate_session = (bool)$rotate_session;
		}

		$this->cookie_domain = StringHelper::getDomain();
		if( !empty($cookie_settings) ) {
			if( isset($cookie_settings["name"]) ) {
				$this->cookie_name = $cookie_settings["name"];
			}
			if( isset($cookie_settings["lifetime"]) ) {
				$this->cookie_lifetime = (int)$cookie_settings["lifetime"];
			}
			if( isset($cookie_settings["secure"]) ) {
				$this->cookie_secure = (int)$cookie_settings["secure"];
			}
			if( isset($cookie_settings["same_site"]) ) {
				$this->cookie_same_site = $cookie_settings["same_site"];
			}
		}
		$this->encryption = (bool)$config->getSectionValue("security", "encrypted_session");
	}

	/**
	 * @param string $session_id
	 * @return void
	 * @throws SystemException
	 */
	public function init(string $session_id): void {
		if( $session_id === "" ) {
			return;
		}

		try {
			$session_repo = App::getInstanceOf(SessionRepository::class);
			$session_data = $session_repo->getAsArray($session_id);
			if( !empty($session_data) ) {
				$this->id = $session_data["id"];
				$this->actor_id = (int)$session_data["actor_id"];
				$this->as_actor = (int)$session_data["as_actor"];
				$this->ip = $session_data["ip"];
				$this->expired = $session_data["expired"];
				$this->created = $session_data["created"];
				$this->updated = ($session_data["updated"] !== "") ? $session_data["updated"] : null;
			}
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Returns the actor of the current session
	 *
	 * @return ActorModel
	 *
	 * @throws SystemException
	 */
	public function getActor(): ActorModel {
		try {
			$session_repo = App::getInstanceOf(SessionRepository::class);
			$actor = App::getInstanceOf(ActorModel::class);
			// do we have an actor?
			if( $this->actor_id > 0 ) {
				$date_time = new DateTime();
				$date_time->modify("+" . $this->cookie_lifetime . " minutes");
				$this->ip = $_SERVER["REMOTE_ADDR"];
				$this->expired = $date_time->format("Y-m-d H:i:s");
				$actor = App::getInstanceOf(ActorModel::class, null, ["id" => $this->actor_id]);
				$session_repo->updateObject($this);
				if( $this->use_cookies ) {
					$this->writeCookie();
				} else {
					$this->writeSession();
				}
			}
			return $actor;
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Try to log in with the given email and password
	 *
	 * @param string $email
	 * @param string $password
	 * @return bool
	 *
	 * @throws \lib\core\exceptions\SystemException
	 */
	public function login(string $email, string $password): bool {
		$permanent = (App::$request->contains("permanent_login") && App::$request->get("permanent_login") === "yes");
		try {
			$session_repo = App::getInstanceOf(SessionRepository::class);
			$actor_repo = App::getInstanceOf(ActorRepository::class);
			$actor = $actor_repo->getByLogin($email);
			if( $actor->id > 0 && password_verify($password, $actor->password) ) {
				$session_id = StringHelper::getGuID();
				$date_time = new DateTime();
				if( $permanent ) {
					$date_time->modify("+" . $this->cookie_lifetime . " minutes");
				} else {
					$date_time->modify("+1 year");
				}
				$this->id = $session_id;
				$this->actor_id = $actor->id;
				$this->ip = $_SERVER["REMOTE_ADDR"];
				$this->expired = $date_time->format("Y-m-d H:i:s");
				$session_repo->createObject($this);
				if( $this->use_cookies ) {
					$this->writeCookie();
				} else {
					$this->writeSession();
				}
				return true;
			}
			$this->error = "E-Mail/Password is incorrect!";
			return false;
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Logout the current actor
	 *
	 * @return void
	 *
	 * @throws SystemException
	 */
	public function logout(): void {
		try {
			$session_repo = App::getInstanceOf(SessionRepository::class);
			if( $this->as_actor > 0 ) {
				$this->as_actor = 0;
				$session_repo->updateObject($this);
				if( $this->use_cookies ) {
					$this->writeCookie();
				} else {
					$this->writeSession();
				}
			} else if( $this->use_cookies ) {
				$session_id = ($this->encryption) ? StringHelper::decrypt($_COOKIE[$this->cookie_name], session_id()) : $_COOKIE[$this->cookie_name];
				if( isset($_COOKIE[$this->cookie_name]) && $session_id === $this->id ) {
					$date_time = new DateTime();
					$date_time->setTimestamp(time() - 3600);
					$session_repo->deleteObject($this);
					$this->actor_id = 0;
					$this->expired = $date_time->format('Y-m-d H:i:s');
					$this->writeCookie();
				}
			} else {
				$session_id = ($this->encryption) ? StringHelper::decrypt($_SESSION[$this->cookie_name], session_id()) : $_SESSION[$this->cookie_name];
				if( isset($_SESSION[$this->cookie_name]) && $session_id === $this->id ) {
					unset($_SESSION[$this->cookie_name]);
				}
			}
			redirect("/");
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Writes the current session into the browser cookies
	 *
	 * @return void
	 * @throws SystemException
	 */
	public function writeCookie(): void {
		$date_time = DateTime::createFromFormat("Y-m-d H:i:s", $this->expired);
		$session_id = ($this->encryption) ? StringHelper::encrypt($this->id) : $this->id;

		$cookie_options = array(
			'expires'  => $date_time->getTimestamp(),
			'path'     => $this->cookie_path,
			'domain'   => $this->cookie_domain,
			'secure'   => $this->cookie_secure,
			'samesite' => $this->cookie_same_site
		);
		setcookie($this->cookie_name, $session_id, $cookie_options);
	}

	/**
	 * Writes the current session from the browser
	 * @return void
	 * @throws SystemException
	 */
	public function writeSession(): void {
		$session_id = ($this->encryption) ? StringHelper::encrypt($this->id, session_id()) : $this->id;
		$_SESSION[$this->cookie_name] = $session_id;
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
