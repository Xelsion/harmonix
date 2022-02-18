<?php

namespace core\abstracts;

abstract class AController {

	protected string $_route;
	protected string $_action;
	protected array $_params = array();

	protected array $_action_list = array();

	public function setRoute( string $route ): void {
		$this->_route = $route;
	}

	public function getRoute(): string {
		return $this->_route;
	}

	public function setAction( string $action ): void {
		$this->_action = $action;
	}

	public function getAction(): string {
		return $this->_action;
	}

	public function addParam( $param ): void {
		$this->_params[] = $param;
	}

	public function getParam(): array {
		return $this->_params;
	}

	protected function addActionFunction( string $action_name, string $function_name ): void {
		$this->_action_list[$action_name] = $function_name;
	}

	protected function getActionFunction( string $action_name ): string {
		return $this->_action_list[$action_name] ?? "";
	}

	protected function hasActionFunction( string $action_name ): bool {
		if( isset($this->_action_list[$action_name]) ) {
			return true;
		}
		return false;
	}
}