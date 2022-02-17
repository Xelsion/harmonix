<?php

namespace core\classes;

use core\abstracts\AResponse;

class ResponseHTML extends AResponse {

	public function getOutput(): string {
		return "<html></html>";
	}

}