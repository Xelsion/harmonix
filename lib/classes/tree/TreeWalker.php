<?php
namespace lib\classes\tree;

/**
 * The TreeWalker class
 * Allows to navigate to a Tree structure represented by an array
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class TreeWalker {

	// all nodes of the tree
	private array $nodes = array();

	/**
	 * The constructor
	 */
	public function __construct() {

	}

	/**
	 * Adds a TreeNode to the Tree structure
	 *
	 * @param TreeNode $node
	 */
	public function addNode( TreeNode $node ): void {
		$this->nodes[$node->id] = $node;
		ksort($this->nodes);
	}

	/**
	 * Returns a node by the given id or null if
	 * the node was not found
	 *
	 * @param int $node_id
	 * @return TreeNode|null
	 */
	public function getNode( int $node_id ): ?TreeNode {
		return $this->nodes[$node_id] ?? null;
	}

    /**
     * Returns the parent node of the node with the given
     * id or null if it has no parent
     *
     * @param int $node_id
     * @return TreeNode|null
     */
    public function getParentOf( int $node_id ): ?TreeNode {
        $node = $this->getNode($node_id);
        return ( !is_null($node) ) ? $this->getNode($node->child_of) : null;
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
	 * Returns all child nodes of the node with the given id
	 * or an empty array if it has none
	 *
	 * @param int|null $node_id
	 * @return array
	 */
	public function getChildrenOf( ?int $node_id ): array {
		$children = array();
		foreach( $this->nodes as $id => $current_node ) {
			if( $current_node->child_of === $node_id ) {
				$children[$id] = $current_node;
			}
		}
		return $children;
	}

    /**
     * Checks if the first node is an ancestor of the second node
     *
     * @param int $node_id
     * @param int $descendant_id
     * @return bool
     */
    public function isNodeAncestorOf( int $node_id, int $descendant_id ) : bool {
        $curr_node = $descendant_id;
        $parent_node = $this->getParentOf($curr_node);
        while( !is_null($parent_node) ) {
            if( $parent_node->id === $node_id ) {
                return true;
            }
            $parent_node = $this->getParentOf($parent_node->id);
        }
        return false;
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
			$ancestors[$parent->id] = $parent;
			$parent = $this->getParentOf($parent->id);
		}
		return $ancestors;
	}

    /**
     * Checks if the first node is a descendant of the second node
     *
     * @param int $node_id
     * @param int $ancestor_id
     * @return bool
     */
    public function isNodeDescendantOf( int $node_id, int $ancestor_id ) : bool {
        return $this->isNodeAncestorOf($ancestor_id, $node_id);
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
                $this->getDescendantsOf($child->id, $descendants);
            }
        }
        return $descendants;
    }

    /**
     * Checks if the first node is a sibling of the second node
     *
     * @param int $node_id
     * @param int $sibling_id
     * @return bool
     */
    public function isNodeSiblingOf( int $node_id, int $sibling_id) : bool {
        $node1 = $this->getNode($node_id);
        $node2 = $this->getNode($sibling_id);
        if( !is_null($node1) && !is_null($node2) ) {
            return ( $node1->child_of === $node2->child_of);
        }
        return false;
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
			if( $node->child_of !== null ) {
				$parent = $this->getParentOf($node->id);
				if( !is_null($parent) ) {
					$siblings = $this->getChildrenOf($parent->id);
				}
			} else {
				foreach( $this->nodes as $id => $current_node ) {
					if( is_null($current_node->child_of) ) {
						$siblings[$id] = $current_node;
					}
				}
			}

			if( $exclude_self && isset($siblings[$node->id]) ) {
				unset($siblings[$node->id]);
			}
		}
		return $siblings;
	}
}
