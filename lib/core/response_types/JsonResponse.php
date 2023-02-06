<?php
namespace lib\core\response_types;

use JsonSerializable;
use lib\core\blueprints\AResponse;

/**
 * The JsonResponse class
 * This class will handle responses in JSON format
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class JsonResponse extends AResponse implements JsonSerializable {
    // the default status for html status headers
    public int $status_code = 200;

    // a parameter which will be used for the JsonSerializable implementation
    private mixed $value;

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

    /**
     * Sets the given value as json encoded string to the response_types output
     *
     * @param mixed $value
     * @return void
     */
    public function setOutput( mixed $value ): void {
        $this->value = $value;
        parent::setOutput(json_encode($this->jsonSerialize()));
    }

    /**
     * Returns a serializable value
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed {
        return $this->value;
    }
}
