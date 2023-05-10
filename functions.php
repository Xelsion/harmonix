<?php
use lib\helper\StringHelper;

/**
 * The class autoloader function
 * Includes the class file of a requested class if it's not loaded.
 * The class file must be in the same directory structure as the namespace is set.
 */
spl_autoload_register(static function($class_name) {
	$file_name = Namespace2Path($class_name);
    if( file_exists(PATH_ROOT . $file_name. ".php") ) {
        require_once PATH_ROOT . $file_name . ".php";
    } else {
        echo "failed to find class[{$class_name}] file: {$file_name}<br />";
    }
}, true, true);

/**
 * Generates a token and saves it into the session
 * returns a hidden form field with the token to match it later
 *
 * @return string
 */
function createCsrfToken(): string {
    $token = StringHelper::getGuID();
    $_SESSION['csrf_token'] = $token;
    return '<input type="hidden" name="csrf_token" value="'.$token.'" />';
}

/**
 * @param string $namespace
 * @return string
 */
function Namespace2Path( string $namespace ): string {
	if( DIRECTORY_SEPARATOR === "/" ) {
		$namespace = str_replace("\\", DIRECTORY_SEPARATOR, $namespace);
	} else {
		$namespace = str_replace("/", DIRECTORY_SEPARATOR, $namespace);
	}
	return $namespace;
}

/**
 * @param string $path
 * @return string
 */
function Path2Namespace( string $path ): string {
	$path = str_replace(array( "..".DIRECTORY_SEPARATOR, ".php" ), "", $path);
	if( DIRECTORY_SEPARATOR === "/" ) {
		$path = str_replace("/", "\\", $path);
	}
	return $path;
}

/**
 * Escapes the given string and converts it to UTF-8
 *
 * @param string|null $string
 * @return string|null
 */
function escaped_string( ?string $string ): ?string {
    if( is_null($string) ) {
        return null;
    }
    if( $string === "" ) {
        return $string;
    }

    $encoding = mb_detect_encoding($string);
    if( $encoding !== "UTF-8" ) {
        $string = iconv($encoding, "UTF-8", $string);
    }
    return htmlentities($string, ENT_HTML5, "UTF-8");
}


/**
 * @param string $url
 * @return void
 */
function redirect( string $url ): void {
	header("Location: https://".$_SERVER["HTTP_HOST"].$url);
	die();
}

/**
 * @param $message
 * @return void
 */
function print_debug( $message ): void {
    if( $message === null ) {
        echo "NULL";
    } elseif( is_object($message) ) {
		echo "<pre>";
		var_dump($message);
		echo "</pre>";
	} elseif( is_array($message) ) {
		echo "<pre>";
		print_r($message);
		echo "</pre>";
	} elseif( is_bool($message) ) {
		echo ( ( $message ) ? "true" : "false" )."<br />";
	} else {
		echo $message."<br />";
	}
}
