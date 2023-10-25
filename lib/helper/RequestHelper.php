<?php

namespace lib\helper;

use lib\core\Request;

readonly class RequestHelper {

	private Request $request;

	public function __construct(Request $request) {
		$this->request = $request;
	}

	/**
	 *
	 * @return array
	 */
	public function getPaginationParams(): array {
		$params = array();
		$params['order'] = $this->request->get("order") ?? "";
		$params['direction'] = $this->request->get("direction") ?? "asc";
		$params['limit'] = ($this->request->contains("limit")) ? (int)$this->request->get("limit") : 25;
		$params['page'] = ($this->request->contains("page")) ? (int)$this->request->get("page") : 1;
		return $params;
	}

	/**
	 * @param array $mapping
	 * @return array
	 */
	public function getFilter(array $mapping = []): array {
		$filter = array();
		$request_filter = $this->request->get("filter");
		if( is_array($request_filter) ) {
			foreach( $request_filter as $key => $value ) {
				if( array_key_exists($key, $mapping) ) {
					$filter[$mapping[$key]] = $value;
				} else {
					$filter[$key] = $value;
				}
			}
		}
		return $filter;
	}

}
