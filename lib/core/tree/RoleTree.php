<?php

namespace lib\core\tree;

use Exception;
use lib\App;
use lib\core\exceptions\SystemException;
use repositories\ActorRoleRepository;

/**
 * The RoleTree class extends TreeWalker
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class RoleTree extends TreeWalker {

	private static ?RoleTree $instance = null;

	/**
	 * The class constructor
	 *
	 * Collect all actor roles and add then to the tree
	 *
	 * @throws SystemException
	 */
	private function __construct() {
		parent::__construct();
		$repository = App::getInstanceOf(ActorRoleRepository::class);
		$actor_roles = $repository->getAll();
		foreach( $actor_roles as $role ) {
			$node = new RoleNode($role);
			$this->addNode($node);
		}
	}

	/**
	 * The initializer for this class
	 *
	 * @return RoleTree
	 */
	public static function getInstance(bool $force_reload = false): RoleTree {
		if( static::$instance === null || $force_reload ) {
			static::$instance = new RoleTree();
		}
		return static::$instance;
	}

	/**
	 * @param int $parent_id
	 * @param array $results
	 * @param int $depth
	 * @return void
	 */
	public function getRolesAsArray(int $parent_id, array &$results, int $depth = 0): void {
		$nodes = $this->getChildrenOf($parent_id);
		foreach( $nodes as $role_node ) {
			$results[] = $role_node->role;
			if( $this->hasChildren($role_node->id) ) {
				$this->getRolesAsArray($role_node->id, $results, ($depth + 1));
			}
		}
	}

	/**
	 * @param int $parent_id
	 * @param string $html
	 * @return void
	 * @throws SystemException
	 */
	public function buildHtmlTree(int $parent_id, string &$html): void {
		try {
			$children = $this->getChildrenOf($parent_id);
			if( !empty($children) ) {
				$html .= '<ul>';
				foreach( $children as $child ) {
					$has_children = $this->hasChildren($child->id);
					$class = ($has_children) ? "contains-children" : "";
					$html .= '<li class="' . $class . '">';
					$html .= $child->name;
					if( $has_children ) {
						$this->buildHtmlTree($child->id, $html);
					}
					$html .= '</li>';
				}
				$html .= '</ul>';
			}
		} catch( Exception $e ) {
			throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

}
