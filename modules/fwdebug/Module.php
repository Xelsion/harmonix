<?php

namespace modules\fwdebug;

use lib\App;
use lib\core\blueprints\AModule;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\core\enums\Subdomain;
use lib\core\exceptions\SystemException;

class Module extends AModule {

	private bool $debugging = true;

	/**
	 * @return void
	 */
	public function boot(): void {
		$env = App::$config->getSectionValue('system', 'environment');
		$this->debugging = (bool)App::$config->getSectionValue($env, 'debug');
	}

	/**
	 * @return string[]
	 */
	public function allowedSubDomains(): array {
		return [Subdomain::ANY];
	}

	/**
	 * @param array $route
	 * @return void
	 */
	public function onAfterRouting(array $route): void {
		if( $this->debugging ) {
			TemplateData::set('fwdebug_controller', $route["controller"]);
			TemplateData::set('fwdebug_controller_method', $route["method"]);
		}
	}

	/**
	 * @param Template $template
	 * @return void
	 * @throws SystemException
	 */
	public function onBeforeResponse(?Template $template): void {
		if( $this->debugging && $template !== null ) {
			TemplateData::set('fwdebug_messages', App::$storage->get("debug"));
			TemplateData::set('fwdebug_actor', App::$curr_actor->first_name . ' ' . App::$curr_actor->last_name);
			TemplateData::set('fwdebug_actor_role', App::$curr_actor_role->name);
			TemplateData::set('fwdebug_sql_entries', App::$analyser->getEntries());
			$debug_template = new Template(__DIR__ . '/templates/fwdebug.html');
			TemplateData::addTemplateToHook('body_bottom', $debug_template);
		}
	}


}