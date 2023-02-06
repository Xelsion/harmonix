<?php
namespace lib\helper;

class ValidationHelper {
    private const regex_street = '/^[a-zA-Z|ÖÜÄöäüß]+([\s|-]?[A-Za-z|ÖÜÄöäüß]+)*\.?$/u';
    private const regex_street_nr = '/^\d+[a-z]?\s?(-\s?\d+[a-z]?)?$/';
    private const regex_zipcode = '/^\d{5}$/';
    private const regex_city = '/^[a-zA-Z|ÖÄÜöäüß]+([\s|-]?[A-Za-z|ÖÜÄöäüß]+)*$/u';

    public static function isValidEmail( string $value ): bool {
        return filter_var( $value, FILTER_VALIDATE_EMAIL );
    }

    public static function isValidNumeric( string $value ): bool {
        return filter_var( $value, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
    }

    public static function isValidInteger( string $value ): bool {
        return filter_var( $value, FILTER_VALIDATE_INT);
    }

    public static function isValidStreet( string $value ): bool {
        return (bool)preg_match( self::regex_street, $value );
    }

    public static function isValidStreetNr( string $value ): bool {
        return (bool)preg_match( self::regex_street_nr, $value );
    }

    public static function isValidZipcode( string $value ): bool {
        return (bool)preg_match( self::regex_zipcode, $value );
    }

    public static function isValidCity( string $value ): bool {
        return (bool)preg_match( self::regex_city, $value );
    }

    public static function isValidPassword( string $value ): bool {
        if( !preg_match( "/[A-Z|ÖÜÄ]+/u", $value ) ) {
            return false;
        }
        if( !preg_match( "/\d+/", $value ) ) {
            return false;
        }
        if( !preg_match( "/[!|§|$|%|&|@|\/|\(|\)|\[|\]|\*|#|\+|\-]+/u", $value ) ) {
            return false;
        }
        if( strlen( $value ) < 8 ) {
            return false;
        }
        return true;
    }
}