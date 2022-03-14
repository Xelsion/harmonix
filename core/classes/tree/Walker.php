<?php

namespace core\classes\tree;

/**
 * The Walker class
 * Allows to navigate to a Tree structure represented by an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Walker {

	// all nodes of the tree
	private array $_nodes = array();

	/**
	 * The constructor
	 */
	public function __construct() {

	}

	/**
	 * Adds a Node to the Tree structure
	 *
	 * @param Node $node
	 */
	public function addNode( Node $node ): void {
		$this->_nodes[$node->_id] = $node;
		ksort($this->_nodes);
	}

	/**
	 * Returns a node by the given id or null if
	 * the node was not found
	 *
	 * @param int $node_id
	 * @return Node|null
	 */
	public function getNode( int $node_id ): ?Node {
		return $this->_nodes[$node_id] ?? null;
	}

	/**
	 * Returns true id the node by the given id das
	 * any child nodes
	 *
	 * @param int|null $node_id
	 * @return bool
	 */
	public function hasChildren( ?int $node_id ): bool {
		$children = $this->getChildrenOf($node_id);
		return ( !empty($children) );
	}

	/**
	 * Returns the parent node of the node with the given
	 * id or null if it has no parent
	 *
	 * @param int $node_id
	 * @return Node|null
	 */
	public function getParentOf( int $node_id ): ?Node {
		$node = $this->getNode($node_id);
		return ( !is_null($node) ) ? $this->getNode($node->_child_of) : null;
	}

	/**
	 * Returns all child nodes of the node with the given id
	 * or an empty array if it has none
	 *
	 * @param int|null $node_id
	 * @return array
	 */
	public function getChildrenOf( ?int $node_id ): array {
		$children = array();
		foreach( $this->_nodes as $id => $current_node ) {
			if( $current_node->_child_of === $node_id ) {
				$children[$id] = $current_node;
			}
		}
		return $children;
	}

	/**
	 * Returns all ancestor nodes of the node with the given id
	 * or an empty array if it has none
	 *
	 * @param int $node_id
	 * @return array
	 */
	public function getAncestorsOf( int $node_id ): array {
		$ancestors = array();
		$parent = $this->getParentOf($node_id);
		while( $parent !== null ) {
			$ancestors[$parent->_id] = $parent;
			$parent = $this->getParentOf($parent->id);
		}
		return $ancestors;
	}

	/**
	 * Returns all sibling nodes of the node with the given id
	 * or an empty array if it has none.
	 * if exclude_self is true it will exclude itself else not
	 *
	 * @param int $node_id
	 * @param bool $exclude_self
	 * @return array
	 */
	public function getSiblingsOf( int $node_id, bool $exclude_self = true ): array {
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

	/**
	 * Returns all descendant nodes of the node with the given id
	 * or an empty array if it has none
	 *
	 * @param int $node_id
	 * @param array $results
	 * @return array
	 */
	public function getDescendantsOf( int $node_id, array &$results = array() ): array {
		$descendants = $results;
		$curr_children = $this->getChildrenOf($node_id);
		foreach( $curr_children as $id => $child ) {
			$descendants[$id] = $child;
			if( $child->hasChildren() ) {
				$this->getDescendantsOf($child->_id, $descendants);
			}
		}
		return $descendants;
	}
}