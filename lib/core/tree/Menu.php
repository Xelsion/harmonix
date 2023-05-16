<?php

namespace lib\core\tree;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;
use lib\core\Router;

/**
 * The Menu class extends TreeWalker
 * Represents a tree structured Menu
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Menu extends TreeWalker {

    public function __construct(private readonly Router $router) {
        parent::__construct();
    }

    /**
     * Adds a MenuItem the tree structure
     *
     * @param MenuItem $item
     */
    public function addMenuItem(MenuItem $item): void {
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
    public function insertMenuItem(int $id, ?int $child_of, string $name, string $target): void {
        $item = new MenuItem($id, $child_of, $name, $target);
        $this->addMenuItem($item);
    }

    /**
     * Returns the menu as html &lt;ul&gt;&lt;li&gt; string
     *
     * @return string
     *
     * @throws SystemException
     */
    public function getAsHtml(): string {
        $html = '';
        $this->buildHtmlTree(null, $html);
        return $html;
    }

    /**
     * builds the &lt;ul&gt;&lt;li&gt; structure from the menu as tree
     *
     * @param int|null $parent_id
     * @param $html
     * @throws SystemException
     */
    public function buildHtmlTree(?int $parent_id, &$html): void {
        try {
            $children = $this->getChildrenOf($parent_id);
            if( !empty($children) ) {
                $html .= '<ul>';
                foreach( $children as $child ) {
                    $route = $this->router->getRouteFor($child->target);
                    if( isset($route["controller"], $route["method"]) && App::$auth->hasAccessTo($route["controller"], $route["method"]) ) {
                        $has_children = $this->hasChildren($child->id);
                        $class = ($has_children) ? "has-children" : "";
                        $html .= '<li class="' . $class . '">';
                        $html .= $child->getLink();
                        if( $has_children ) {
                            $this->buildHtmlTree($child->id, $html);
                        }
                        $html .= '</li>';
                    }
                }
                $html .= '</ul>';
            }
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}
