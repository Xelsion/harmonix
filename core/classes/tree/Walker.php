<?php

namespace core\classes\tree;

class Walker {

    private array $_nodes = array();

    public function __construct() {

    }

    public function addNode( Node $node ) : void {
        $this->_nodes[$node->_id] = $node;
    }

    public function getNode( int $node_id ) : ?Node {
        return $this->_nodes[$node_id] ?? null;
    }

    public function hasChildren( ?int $node_id) : bool {
        $children = $this->getChildrenOf($node_id);
        return ( !empty($children) );
    }

    public function getParentOf( int $node_id ) : ?Node {
        $node = $this->getNode($node_id);
        return ( !is_null($node) ) ? $this->getNode($node->_child_of) : null;
    }

    public function getChildrenOf( ?int $node_id ) : array {
        $children = array();
        foreach( $this->_nodes as $id => $current_node ) {
            if( $current_node->_child_of === $node_id) {
                $children[$id] = $current_node;
            }
        }
        return $children;
    }

    public function getAncestorsOf( int $node_id ) : array {
        $ancestors = array();
        $parent = $this->getParentOf( $node_id );
        while( $parent !== null ) {
            $ancestors[$parent->_id] = $parent;
            $parent = $this->getParentOf($parent->id);
        }
        return $ancestors;
    }

    public function getSiblingsOf( int $node_id, $exclude_self = true ) : array {
        $siblings = array();
        $node = $this->getNode($node_id);
        if( !is_null($node) ) {
            if( $node->_child_of !== null ) {
                $parent = $this->getParentOf($node->_id);
                if( !is_null($parent) ) {
                    $siblings = $this->getChildrenOf($parent->_id);
                }
            } else {
                foreach( $this->_nodes as $id => $current_node ) {
                    if( is_null($current_node->_child_of) ) {
                        $siblings[$id] = $current_node;
                    }
                }
            }

            if( $exclude_self && isset($siblings[$node->_id]) ) {
                unset($siblings[$node->_id]);
            }
        }
        return $siblings;
    }

    public function getDescendantsOf( int $node_id, array &$results = array() ) : array {
        $descendants = $results;
        $curr_children = $this->getChildrenOf( $node_id );
        foreach( $curr_children as $id => $child ) {
            $descendants[$id] = $child;
            if( $child->hasChildren() ) {
                $this->getDescendantsOf($child->_id, $descendants);
            }
        }
        return $descendants;
    }

}