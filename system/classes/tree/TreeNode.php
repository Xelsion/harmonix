<?php

namespace system\classes\tree;

/**
 * The TreeNode class
 * Can be added to the Tree TreeWalker
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class TreeNode {

	// the node id
	public int $_id = 0;
	// the id of its parent
	public ?int $_child_of = null;
	// the name of the node
	public string $_name = "";

	/**
	 * The constructor creates a TreeNode
	 *
	 * @param int $id
	 * @param int|null $child_of
	 * @param string $name
	 */
	public function __construct( int $id, ?int $child_of, string $name ) {
		$this->_id = $id;
		$this->_child_of = $child_of;
		$this->_name = $name;
	}

}