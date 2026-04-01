<?php

namespace modules\fwinfos;

use lib\App;
use lib\core\blueprints\AModule;
use lib\core\classes\Analyser;
use lib\core\classes\StopWatch;
use lib\core\classes\TemplateData;
use lib\core\exceptions\SystemException;

class Module extends AModule {

	private StopWatch $timer;

	/**
	 * @return void
	 * @throws SystemException
	 */
	public function boot(): void {
		$this->timer = App::getInstanceOf(Analyser::class);
	}

	public function onStart(): void {
		$this->timer->start();
	}

	public function onBeforeResponse(string &$output): void {
		$this->timer->stop();
		$elapsed_time = $this->timer->getElapsedTime()->format("ms");
		$is_cached = "false";
		$output = str_replace(array(
			"{{system_message}}",
			"{{build_time}}",
			"{{is_cached}}"
		), array(TemplateData::getSystemMessage(), $elapsed_time, $is_cached), $output);
	}
}