<?php

namespace system\classes\tree;

use system\System;

/**
 * The Menu class extends TreeWalker
 * Represents a tree structured Menu
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Menu extends TreeWalker {

	/**
	 * Adds a MenuItem the tree structure
	 *
	 * @param MenuItem $item
	 */
	public function addMenuItem( MenuItem $item ): void {
		$this->addNode($item);
	}

	/**
	 * Created a new MenuItem and adds it to the
	 * tree structure
	 *
	 * @param int $id
	 * @param int|null $child_of
	 * @param string $name
	 * @param string $target
	 */
	public function insertMenuItem( int $id, ?int $child_of, string $name, string $target ): void {
		$item = new MenuItem($id, $child_of, $name, $target);
		$this->addMenuItem($item);
	}

	/**
	 * Returns the menu as html <ul><li> string
	 *
	 * @return string
	 */
	public function getAsHtml(): string {
		$html = '';
		$this->buildHtmlTree(null, $html);
		return $html;
	}

	/**
	 * builds the <ul><li> structure from the menu as tree
	 *
	 * @param int|null $parent_id
	 * @param $html
	 */
	public function buildHtmlTree( ?int $parent_id, &$html ): void {
		$children = $this->getChildrenOf($parent_id);
		if( !empty($children) ) {
			$html .= '<ul>';
			foreach( $children as $child ) {
                $route = System::$Core->router->getRouteFor($child->target);
                if( System::$Core->auth->hasAccessTo($route["controller"], $route["method"]) ) {
                    $has_children = $this->hasChildren($child->id);
                    $class = ( $has_children ) ? "has-children" : "";
                    $html .= '<li class="'.$class.'">';
                    $html .= $child->getLink();
                    if( $has_children ) {
                        $this->buildHtmlTree($child->id, $html);
                    }
                    $html .= '</li>';
                }
			}
			$html .= '</ul>';
		}
	}

}
