<?php

namespace lib\core\response_types;

use JsonException;
use JsonSerializable;
use lib\core\abstracts\AResponse;
use lib\core\enums\HttpResponseCode;
use lib\core\exceptions\SystemException;

/**
 * The JsonResponse class
 * This class will handle responses in JSON format
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class JsonResponse extends AResponse implements JsonSerializable {
	// the default status for html status headers
	public HttpResponseCode $status_code;

	// The internal raw payload data
	private mixed $value;

	/**
	 * @param mixed|null $content
	 * @throws SystemException
	 */
	public function __construct(mixed $content = null) {
		$this->status_code = HttpResponseCode::Ok;
		if( $content !== null ) {
			$this->setOutput($content);
		}
	}

	/**
	 * Sets the header status code of this response.
	 * Matches the implementation of HtmlResponse and TextResponse.
	 *
	 * @param HttpResponseCode $status_code
	 * @return void
	 */
	public function withHeader(HttpResponseCode $status_code): void {
		$this->status_code = $status_code;
	}

	/**
	 * @inherite
	 */
	public function setHeaders(): void {
		header($this->status_code->toString());
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Content-Type: application/json; charset=utf-8");
	}

	/**
	 * Sets the given value as json encoded string to the response_types output
	 *
	 * @param mixed $output
	 * @return void
	 * @throws SystemException
	 */
	public function setOutput(mixed $output): void {
		try {
			$this->value = $output;

			$json_string = json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
			parent::setOutput($json_string);
		} catch( JsonException $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage());
		}
	}

	/**
	 * Returns a serializable value
	 *
	 * @return mixed
	 */
	public function jsonSerialize(): mixed {
		if( $this->value instanceof JsonSerializable ) {
			return $this->value->jsonSerialize();
		}
		return $this->value;
	}
}
