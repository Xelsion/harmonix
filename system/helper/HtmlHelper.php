<?php

namespace system\helper;

use models\ActorRole;

class HtmlHelper {

    public static function getRoleOptions( ActorRole $curr_role, int $selected, string &$output ) : void {
        $output .= '<option value="'. $curr_role->id.'"'. (($curr_role->id === $selected) ? ' selected="selected"' : '') .'>'. escaped_html($curr_role->name).'</option>';
        $children = $curr_role->getChildren();
        foreach( $children as $child ) {
            static::getRoleOptions($child, $selected, $output);
        }
    }

    public static function getPagination( int $curr_page, int $num_entries, int $limit, string &$output ) {
        $num_pages = ceil( $num_entries / $limit );
        if( (int)$num_pages === 1 ) {
            return;
        }

        $output = '<ul class="pagination">';
        if( $curr_page > 1 ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page-1).'" class="button button-default">&lt;</button></li>';
        }
        for( $i = 1; $i <= $num_pages; $i++ ) {
            $class = ( $i === $curr_page ) ? 'button-positiv' : 'button-default';
            $output .= '<li><button type="submit" name="page" value="'.$i.'" class="button '.$class.'">'. $i .'</button></li>';
        }
        if( $curr_page < $num_pages ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page+1).'" class="button button-default">&gt;</button></li>';
        }
        $output .= '</ul>';
    }

}