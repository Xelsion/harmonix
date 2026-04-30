<?php

namespace modules\fwinfos;

use lib\App;
use lib\core\blueprints\AModule;
use lib\core\classes\Analyser;
use lib\core\classes\StopWatch;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\Subdomain;
use lib\core\exceptions\SystemException;

class Module extends AModule {

	private StopWatch $timer;

	/**
	 * @return Subdomain[]
	 */
	public function allowedSubDomains(): array {
		return [Subdomain::WWW];
	}

	public function controllerDirectories(): array {
		return array("www" => PATH_MODULES . "fwinfos/controller");
	}

	/**
	 * @return void
	 * @throws SystemException
	 */
	public function boot(): void {
		$this->timer = App::getInstanceOf(Analyser::class);
	}

	/**
	 * @return void
	 */
	public function onStart(): void {
		$this->timer->start();
	}

	/**
	 * @param ?Template $template
	 * @return void
	 * @throws SystemException
	 */
	public function onBeforeResponse(?Template $template): void {
		if( $template !== null ) {
			$this->timer->stop();
			$elapsed_time = $this->timer->getElapsedTime()->format("ms");
			TemplateData::addHookName('build_time');
			TemplateData::set('build_time', $elapsed_time);
			TemplateData::addTemplateToHook('footer', new Template(__DIR__ . '/templates/fwinfo.html'));
		}
	}
}