<?php

namespace lib\core\classes;

use lib\core\enums\SystemMessageType;
use lib\core\exceptions\SystemException;

/**
 * This class can hold key => value pairs that
 * will be shared by all used templates
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class TemplateData {

	// the data storage for the template
	public static array $data = array();

	// scripts and css that will be added to html head section
	private static array $header = array("css" => array(), "script" => array());

	// scripts that will be added to the end ob the html body section
	private static array $footer = array("script" => array());

	private static ?string $system_message = null;

	private static array $hooks = array();

	private static array $hook_names = array(
		"head_meta",
		"head_css",
		"head_js",

		"body_top",
		"body",
		"body_bottom",
		"body_js",

		"nav_before",
		"nav",
		"nav_after",

		"header_before",
		"header",
		"header_after",

		"main_before",
		"main_top",
		"main",
		"main_bottom",
		"main_after",

		"footer_before",
		"footer",
		"footer_after"
	);

	/**
	 * adds a hook the hook_names
	 *
	 * @param string $hook
	 * @return void
	 * @throws SystemException
	 */
	public static function addHookName(string $hook): void {
		if( in_array($hook, self::$hook_names, true) ) {
			throw new SystemException(__FILE__, __LINE__, "Template hook '{$hook}' already exists!");
		}
		self::$hook_names[] = $hook;
	}

	/**
	 * @param string $hook
	 * @param string $html
	 * @return void
	 * @throws SystemException
	 */
	public static function setHook(string $hook, string $html): void {
		if( !in_array($hook, self::$hook_names, true) ) {
			throw new SystemException(__FILE__, __LINE__, "Template hook '{$hook}' is not defined in allowed_blocks!");
		}
		self::$hooks[$hook] = $html;
	}

	/**
	 * @param string $hook
	 * @return string
	 * @throws SystemException
	 */
	public static function getHook(string $hook): string {
		if( !in_array($hook, self::$hook_names, true) ) {
			throw new SystemException(__FILE__, __LINE__, "Template hook '{$hook}' is not defined in allowed_blocks!");
		}
		return self::$hooks[$hook] ?? "";
	}

	/**
	 * @param string $hook
	 * @param string $html
	 * @return void
	 * @throws SystemException
	 */
	public static function addToHook(string $hook, string $html): void {
		if( !in_array($hook, self::$hook_names, true) ) {
			throw new SystemException(__FILE__, __LINE__, "Template hook '{$hook}' is not defined in allowed_blocks!");
		}
		if( !isset(self::$hooks[$hook]) ) {
			self::$hooks[$hook] = "";
		}
		self::$hooks[$hook] .= $html;
	}

	/**
	 * @param string $hook
	 * @param Template $template
	 * @return void
	 * @throws SystemException
	 */
	public static function addTemplateToHook(string $hook, Template $template): void {
		self::addToHook($hook, $template->parse());
	}


	/**
	 * @param string $hook
	 * @return void
	 * @throws SystemException
	 */
	public static function renderHook(string $hook): void {
		if( !in_array($hook, self::$hook_names, true) ) {
			throw new SystemException(__FILE__, __LINE__, "Template hook '{$hook}' is not defined in allowed_blocks!");
		}
		echo self::getHook($hook);
	}

	/**
	 * @param string $hook
	 * @return bool
	 */
	public static function hasHook(string $hook): bool {
		return array_key_exists($hook, self::$hooks);
	}

	/**
	 * @param string $hook
	 * @return void
	 * @throws SystemException
	 */
	public static function clearHook(string $hook): void {
		if( !in_array($hook, self::$hook_names, true) ) {
			throw new SystemException(__FILE__, __LINE__, "Template hook '{$hook}' is not defined in allowed_blocks!");
		}
		unset(self::$hooks[$hook]);
	}

	/**
	 * Adds a key => value pair to the data storage
	 *
	 * @param $key
	 * @param $value
	 */
	public static function set(string $key, mixed $value, bool $raw_html = false): void {
		if( is_string($value) && !$raw_html ) {
			self::$data[$key] = self::escapeValue($value);
			return;
		}
		self::$data[$key] = $value;
	}

	/**
	 * Returns the value vom the data storage by the given key
	 * or null if the key was not found.
	 *
	 * @param $key
	 * @return mixed|null
	 */
	public static function get(string $key): mixed {
		return static::$data[$key] ?? null;
	}

	/**
	 * Checks if the given key exists in the data array and
	 * returns true if it exists else false
	 *
	 * @param $key
	 * @return bool
	 */
	public static function contains(string $key): bool {
		return array_key_exists($key, static::$data);
	}

	/**
	 * Adds the given css to the headers css array
	 *
	 * @param string $value
	 */
	public static function addHeaderCss(string $value): void {
		if( !in_array($value, static::$header["css"], true) ) {
			static::$header["css"][] = $value;
		}
	}

	/**
	 * Returns all css added to the headers css array in an array
	 *
	 * @return array
	 */
	public static function getHeaderCss(): array {
		return static::$header["css"];
	}

	/**
	 * Adds the given script to the headers script array
	 *
	 * @param string $value
	 * @param bool $async
	 */
	public static function addHeaderScript(string $value, bool $async = false): void {
		static::$header["script"][$value] = $async;
	}

	/**
	 * Returns all scripts added to the headers script array in an array
	 *
	 * @return array
	 */
	public static function getHeaderScripts(): array {
		return static::$header["script"];
	}

	/**
	 * Adds the given script to the footer script array
	 *
	 * @param string $value
	 * @param bool $async
	 */
	public static function addFooterScript(string $value, bool $async = false): void {
		static::$footer["script"][$value] = $async;
	}

	/**
	 * Returns all scripts added to the footer script array in an array
	 *
	 * @return array
	 */
	public static function getFooterScripts(): array {
		return static::$footer["script"];
	}

	/**
	 * @param string $content
	 * @return void
	 * @throws SystemException
	 */
	public static function setSystemMessage(string $message, SystemMessageType $type = SystemMessageType::SUCCESS): void {
		$system_message = new Template(PATH_VIEWS_ROOT . "snippets/system_message.html");
		self::set("system_message_type", $type->toString());
		self::set("system_message", $message);
		static::$system_message = $system_message->parse();
	}

	/**
	 * @return array
	 */
	public static function getSystemMessage(): mixed {
		return static::$system_message;
	}


	private static function escapeValue(mixed $value): mixed {
		if( is_string($value) ) {
			return escaped_string($value);
		}

		if( is_array($value) ) {
			foreach( $value as $k => $v ) {
				$value[$k] = self::escapeValue($v);
			}
		}

		return $value;
	}


}
