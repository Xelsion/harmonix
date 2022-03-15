<?php

namespace core\helper;

use models\ActorRole;

class HtmlHelper {

    public static function getRoleOptions( ActorRole $curr_role, int $selected, string &$output ) : void {
        $output .= '<option value="'. $curr_role->id.'"'. (($curr_role->id === $selected) ? ' selected="selected"' : '') .'>'. escaped_html($curr_role->name).'</option>';
        $children = $curr_role->getChildren();
        foreach( $children as $child ) {
            static::getRoleOptions($child, $selected, $output);
        }
    }

}