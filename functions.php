<?php
spl_autoload_register(static function( $class_name ) {
	$class_name = Namespace2Path($class_name);
	require_once PATH_ROOT . $class_name . ".php";
});

function Namespace2Path( string $namespace ): string {
	if( DIRECTORY_SEPARATOR === "/" ) {
		$namespace = str_replace("\\", DIRECTORY_SEPARATOR, $namespace);
	} else {
		$namespace = str_replace("/", DIRECTORY_SEPARATOR, $namespace);
	}
	return $namespace;
}

function Path2Namespace( string $path ): string {
	$path = str_replace(array( "..".DIRECTORY_SEPARATOR, ".php" ), "", $path);
	if( DIRECTORY_SEPARATOR === "/" ) {
		$path = str_replace("/", "\\", $path);
	}
	return $path;
}

function escaped_html( ?string $string ): ?string {
    if( is_null($string) ) {
        return null;
    }
    if( mb_detect_encoding($string) === "UTF-8") {
        $string = utf8_decode($string);
    }
    $string = utf8_encode($string);
    return htmlentities($string, ENT_HTML5, "UTF-8");
}

function redirect( string $url ): void {
	header("Location: https://".$_SERVER["HTTP_HOST"].$url);
	die();
}

function print_debug( $message ) {
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
