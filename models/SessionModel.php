<?php
namespace models;

use DateTime;
use lib\App;
use models\entities\Session;
use lib\classes\Configuration;
use lib\helper\StringHelper;
use lib\manager\ConnectionManager;

use Exception;
use lib\exceptions\SystemException;

/**
 * The SessionModel
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class SessionModel extends Session {

    // Defines if the cookie will be encrypted or not
    public bool $encryption = false;

    // The name of the cookie
    public string $cookie_name = "session";

    // The cookie path
    public string $cookie_path = "/";

    // The cookie domain
    public string $cookie_domain = "";

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
     */
    public function __construct( Configuration $config ) {
        parent::__construct($config);

        $cookie_settings = $config->getSection("cookie");
        if( !empty($cookie_settings) ) {
            $this->cookie_name = $cookie_settings["name"];
            $this->cookie_domain = $cookie_settings["domain"];
            $this->cookie_lifetime = $cookie_settings["lifetime"];
            $this->cookie_secure = $cookie_settings["secure"];
            $this->cookie_same_site = $cookie_settings["same_site"];
        }
        $this->encryption = (bool)$config->getSectionValue("security", "encrypted_session");
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
            $actor = App::getInstance(ActorModel::class);
            // do we have an actor?
            if( $this->actor_id > 0 ) {
                $date_time = new DateTime();
                $date_time->modify("+".$this->cookie_lifetime." minutes");
                $this->ip = $_SERVER["REMOTE_ADDR"];
                $this->expired = $date_time->format("Y-m-d H:i:s");
                $actor = App::getInstance(ActorModel::class, null, ["id" => $this->actor_id]);

                $this->update();
                $this->writeCookie();
            }
            return $actor;
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
	}

    /**
     * Try to log in with the given email and password
     *
     * @param string $email
     * @param string $password
     * @return bool
     *
     * @throws SystemException
     */
	public function login( string $email, string $password ): bool {
        try {
            $permanent = ( array_key_exists("permanent_login", $_POST) && $_POST["permanent_login"] === "yes");
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
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
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
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
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
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
