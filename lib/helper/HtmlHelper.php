<?php
namespace lib\helper;

use Exception;
use models\ActorRoleModel;

readonly class HtmlHelper {

    /**
     * @param ActorRoleModel $curr_role
     * @param int $selected
     * @param string $output
     *
     * @return void
     *
     * @throws \lib\core\exceptions\SystemException
     */
    public static function getRoleOptions( ActorRoleModel $curr_role, int $selected, string &$output ) : void {
        $output .= '<option value="'. $curr_role->id.'"'. (($curr_role->id === $selected) ? ' selected="selected"' : '') .'>'. escaped_string($curr_role->name).'</option>';
        $children = $curr_role->getChildren();
        foreach( $children as $child ) {
            static::getRoleOptions($child, $selected, $output);
        }
    }

    /**
     * @param int $curr_page
     * @param int $num_entries
     * @param int $limit
     * @param string $output
     *
     * @return void
     */
    public static function getPagination( int $curr_page, int $num_entries, int $limit, string &$output ): void {
        $num_pages = ceil( $num_entries / $limit );
        if( (int)$num_pages === 1 ) {
            return;
        }

        $output = '<ul class="pagination">';

        // back to page 1
        if( $curr_page > 10 ) {
            $output .= '<li><button type="submit" name="page" value="1" class="button button-default">&lt;&lt;</button></li>';
        }

        // 100 page back
        if( $curr_page > 100 ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page-100).'" class="button button-default">-100</button></li>';
        }

        // 50 page back
        if( $curr_page > 50 ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page-50).'" class="button button-default">-50</button></li>';
        }


        // 10 page back
        if( $curr_page > 10 ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page-10).'" class="button button-default">-10</button></li>';
        }

        // 1 page back
        if( $curr_page > 1 ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page-1).'" class="button button-default">&lt;</button></li>';
        }

        // direct page numbers
        $num_buttons = ( $num_pages <= 10 ) ? (int)$num_pages : 9;
        $start_button = ( $curr_page <= 5 ) ? 1 : $curr_page - 4;
        $end_button = ( $start_button + $num_buttons > $num_pages ) ? $num_pages : $start_button + $num_buttons;
        for( $i = $start_button; $i <= $end_button; $i++ ) {
            $class = ( $i === $curr_page ) ? 'button-positiv' : 'button-default';
            $output .= '<li><button type="submit" name="page" value="'.$i.'" class="button '.$class.'">'. $i .'</button></li>';
        }

        // 1 page forward
        if( $curr_page < $num_pages ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page+1).'" class="button button-default">&gt;</button></li>';
        }

        // 10 page forward
        if( $curr_page+10 < $num_pages ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page+10).'" class="button button-default">+10</button></li>';
        }

        // 50 page forward
        if( $curr_page+50 < $num_pages ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page+50).'" class="button button-default">+50</button></li>';
        }

        // 100 page forward
        if( $curr_page+100 < $num_pages ) {
            $output .= '<li><button type="submit" name="page" value="'.($curr_page+100).'" class="button button-default">+100</button></li>';
        }

        // to the last page
        if( $num_pages > 10 ) {
            $output .= '<li><button type="submit" name="page" value="'.$num_pages.'" class="button button-default">&gt;&gt;</button></li>';
        }

        $output .= '</ul>';
    }

    /**
     * @return string
     */
    public static function generateFormToken(): string {
        try {
            if( !isset($_SESSION["form_token"]) ) {
                $token = StringHelper::getGuID();
                $_SESSION["form_token"] = $token;
            } else {
                $token = $_SESSION["form_token"];
            }
            return '<input type="hidden" name="csrf_token" value="'.$token.'" />';
        } catch( Exception ) {
            return "";
        }
    }

    /**
     * @return void
     */
    public static function deleteFormToken(): void {
        if( isset($_SESSION["form_token"]) ) {
            unset($_SESSION["form_token"]);
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public static function validateFormToken( string $id ): bool {
        return (isset($_SESSION["form_token"]) && $id === $_SESSION["form_token"]);
    }

}
