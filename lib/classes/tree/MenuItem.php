<?php
namespace lib\classes\tree;

/**
 * The MenuItem class extends the TreeNode
 * Can be added to the Menu
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class MenuItem extends TreeNode {

	// the target url of this menu item
	public string $target = "";

	/**
	 * The constructor creates a MenuItem
	 *
	 * @param int $id
	 * @param int|null $child_of
	 * @param string $name
	 * @param string $target
	 */
	public function __construct( int $id, ?int $child_of, string $name, string $target ) {
		parent::__construct($id, $child_of, $name);
		$this->target = $target;
	}

	/**
	 * Returns the target url in a html <a> tag
	 *
	 * @param string $class
	 * @return string
	 */
	public function getLink( string $class = "" ) {
		return sprintf('<a href="%s" class="%s">%s</a>', $this->target, $class, $this->name);
	}

}
