<?php

namespace system\classes;

/**
 * The Configuration type singleton
 * Collect all the configurations and stores them in an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Language {

    private static ?Language $_instance = null;

    private array $_lang;

    private function __construct( $lang = "de" ) {
        $this->_lang = parse_ini_file(PATH_ROOT."lang-".$lang.".ini", true);
    }

    /**
     * The initializer for this class
     *
     * @param string $language_key
     *
     * @return Language
     */
    public static function getInstance( string $language_key = "de" ): Language {
        if( static::$_instance === null ) {
            static::$_instance = new Language( $language_key );
        }
        return static::$_instance;
    }

    /**
     * Returns a specific section of the configuration
     *
     * @param string $name
     *
     * @return array
     */
    public function getSection( string $name ): array {
        return $this->_lang[$name] ?? array();
    }

    /**
     * Returns the value of the given key in the given section_name
     *
     * @param string $section_name
     * @param string $key
     *
     * @return string|null
     */
    public function getValue( string $section_name, string $key ): ?string {
        $section = $this->getSection($section_name);
        if( array_key_exists($key, $section) ) {
            return escaped_html($section[$key]);
        }
        return null;
    }
}