<?php

namespace lib\classes\responses;

use lib\abstracts\AResponse;

/**
 * A Response for HTML content
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class HtmlResponse extends AResponse {

	// the default status for html status headers
	public int $status_code = 200;

    /**
     * The class constructor
     *
     * @param string $content
     */
    public function __construct( string $content = "" ) {
        if( $content != "" ) {
            $this->setOutput($content);
        }
    }

    public function withHeader( int $status_code ): void {
        $this->status_code = $status_code;
    }

	/**
	 * @inherite
	 */
	public function setHeaders(): void {
		header("Content-Type: text/html; charset=utf-8");
		switch( $this->status_code ) {
            case 400:
                header("HTTP/1.1 400 Bad Request");
                break;
            case 401:
                header("HTTP/1.1 401 Unauthorized");
                break;
			case 403:
				header("HTTP/1.1 403 Forbidden");
				break;
			case 404:
				header("HTTP/1.1 404 Not Found");
				break;
            case 500:
                header("HTTP/1.1 500 Internal Server Error");
                break;
			default:
				header("HTTP/1.1 200 OK");
		}
	}

}
