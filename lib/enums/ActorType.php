<?php
namespace lib\enums;

enum ActorType: int {

    case Developer = 1;
    case User = 2;

    /**
     * Returns a string representing the
     * @return string
     */
    public function toString(): string {
        return match($this) {
            self::Developer => "Developer",
            self::User => "User"
        };
    }
}