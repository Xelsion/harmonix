<?php
namespace lib\core;

use lib\core\classes\KeyValuePairs;
use lib\core\exceptions\SystemException;

/**
 * The Request Type setAsSingleton
 * represents the requested URL
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Request {

    private string $request_uri;
    private string $request_method;
	// GET, POST & FILES data from the request
	private array $form;
    private array $files;

    /**
     * The class constructor
     * sets the current requested uri
     * calls the method initController()
     * @throws \lib\core\exceptions\SystemException
     */
	public function __construct( public KeyValuePairs $data ) {
        //$this->data = App::getInstanceOf(KeyValuePairs::class);

        $this->request_uri = $_SERVER["REQUEST_URI"]??"";
        $this->request_method = $_SERVER['REQUEST_METHOD']??"";

        if( isset($_SESSION["csrf_token"]) && isset($_POST['csrf_token']) ) {
            if( $_POST['csrf_token'] && $_SESSION["csrf_token"] ) {
                foreach( $_GET as $key => $value ) {
                    $this->form[$key] = $value;
                    $this->data->set($key, $value);
                }
                foreach( $_POST as $key => $value ) {
                    $this->form[$key] = $value;
                    $this->data->set($key, $value);
                }
                foreach( $_FILES as $key => $value ) {
                    $this->files[$key] = $value;
                    $this->data->set($key, $value);
                }
            }
        }
	}

    /**
     * Returns the requested uri
     *
     * @param string $uri
     * @return void
     */
    public function setRequestUri( string $uri ): void {
        $this->request_uri = $uri;
    }

	/**
	 * Returns the requested uri
	 *
	 * @return string
	 */
	public function getRequestUri(): string {
		return $this->request_uri;
	}

	/**
	 * Returns the requested method
	 *
	 * @return string
	 */
	public function getRequestMethod(): string {
		return $this->request_method;
	}

	/**
	 * Returns the remote IP address
	 *
	 * @return string
	 */
	public function getRemoteIP(): string {
		return $_SERVER["REMOTE_ADDR"];
	}

	/**
	 * Returns all submitted key => value pairs
	 *
	 * @return array
	 */
	public function getPosts(): array {
		return $this->form;
	}

    /**
     * Returns all submitted key => value pairs
     *
     * @return array
     */
    public function getFiles(): array {
        return $this->files;
    }

    /**
     * Returns the value from the submitted pairs
     * by its key
     *
     * @param string $key
     *
     * @return mixed
     */
	public function get( string $key ): mixed {
		return $this->form[$key] ?? null;
	}

    /**
     * Returns if a key exists in the form data
     *
     * @param string $key
     *
     * @return bool
     */
    public function has( string $key ): bool {
        return isset($this->form[$key]);
    }

	/**
	 * Split the requested uri into parts and
	 * returns them as an array
	 *
	 * @return array
	 */
	public function getRequestParts(): array {
		return preg_split("/\//", $this->getRequestUri(), -1, PREG_SPLIT_NO_EMPTY);
	}
}
