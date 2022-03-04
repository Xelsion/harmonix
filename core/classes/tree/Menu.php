<?php

namespace core\classes\tree;

class Menu extends Walker {

    public function addMenuItem( MenuItem $item ) : void {
        $this->addNode($item);
    }

    public function insertMenuItem( int $id, ?int $child_of, string $name, string $target ) : void {
        $item = new MenuItem( $id, $child_of, $name, $target );
        $this->addMenuItem($item);
    }

    public function getAsHtml() : string {
        $html = '';
        $this->buildHtmlTree( null, $html);
        return $html;
    }

    public function buildHtmlTree( ?int $parent_id, &$html ) : void {
        $children = $this->getChildrenOf($parent_id);
        if( !empty($children) ) {
            $html .= '<ul>';
            foreach( $children as $child ) {
                $has_children = $this->hasChildren($child->_id);
                $class = ( $has_children ) ? "has-children" : "";
                $html .= '<li class="'.$class.'">';
                $html .= $child->getLink();
                if( $has_children ) {
                    $this->buildHtmlTree($child->_id, $html);
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
    }

}