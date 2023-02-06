<?php
namespace lib\core\enums;

enum ActorRole: int {
    case Administrator = 1;
    case Moderator = 2;
    case Member = 3;
    case Guest = 4;

    /**
     * Returns a string representing the
     *
     * @return string
     */
    public function toString(): string {
        return match($this) {
            self::Administrator => "Administrator",
            self::Moderator => "Moderator",
            self::Member => "Member",
            self::Guest => "Guest"
        };
    }

}