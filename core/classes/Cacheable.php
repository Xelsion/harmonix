<?php

namespace core\classes;

use core\helper\StringHelper;

class Cacheable extends File {


	public function saveToCache( string $content ) {
		$this->setContent(StringHelper::encrypt($content));
	}

	public function loadFromCache() {
		$content = StringHelper::decrypt($this->getContent());
	}

	public function cacheExists() {

	}

}