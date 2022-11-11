<?php

namespace system\classes\responses;

use system\abstracts\AResponse;

/**
 * The JsonResponse class
 * This class will handle responses in JSON format
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class JsonResponse extends AResponse {
    // the default status for html status headers
    public int $status_code = 200;

    /**
     * @inherite
     */
    public function setHeaders(): void {
        header("Content-Type: application/json; charset=utf-8");
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
