<?php
/**
 * Graph data structure
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Data;

class Graph
{
    /**#@+
     * Search modes
     */
    const DIRECTIONAL     = 1;
    const INVERSE         = 2;
    const NON_DIRECTIONAL = 3;
    /**#@-*/

    /**
     * Registry of nodes
     *
     * @var array
     */
    protected $_nodes = array();

    /**
     * Declared relations directed "from" "to"
     *
     * @var array
     */
    protected $_from = array();

    /**
     * Inverse relations "to" "from"
     *
     * @var array
     */
    protected $_to = array();

    /**
     * Validate consistency of the declared structure and assign it to the object state
     *
     * @param array $nodes plain array with node identifiers
     * @param array $relations array of 2-item plain arrays, which represent relations of nodes "from" "to"
     */
    public function __construct(array $nodes, array $relations)
    {
        foreach ($nodes as $node) {
            $this->_assertNode($node, false);
            $this->_nodes[$node] = $node;
        }
        foreach ($relations as $pair) {
            list($fromNode, $toNode) = $pair;
            $this->addRelation($fromNode, $toNode);
        }
    }

    /**
     * Set a relation between nodes
     *
     * @param string|int $fromNode
     * @param string|int $toNode
     * @return \Magento\Data\Graph
     * @throws \InvalidArgumentException
     */
    public function addRelation($fromNode, $toNode)
    {
        if ($fromNode == $toNode) {
            throw new \InvalidArgumentException("Graph node '{$fromNode}' is linked to itself.");
        }
        $this->_assertNode($fromNode, true);
        $this->_assertNode($toNode, true);
        $this->_from[$fromNode][$toNode] = $toNode;
        $this->_to[$toNode][$fromNode] = $fromNode;
        return $this;
    }

    /**
     * Export relations between nodes. Can return inverse relations
     *
     * @param int $mode
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getRelations($mode = self::DIRECTIONAL)
    {
        switch ($mode) {
            case self::DIRECTIONAL:
                return $this->_from;
            case self::INVERSE:
                return $this->_to;
            case self::NON_DIRECTIONAL:
                $graph = $this->_from;
                foreach ($this->_to as $idTo => $relations) {
                    foreach ($relations as $idFrom) {
                        $graph[$idTo][$idFrom] = $idFrom;
                    }
                }
                return $graph;
            default:
                throw new \InvalidArgumentException("Unknown search mode: '{$mode}'");
        }
    }

    /**
     * Find a cycle in the graph
     *
     * Returns first/all found cycle
     * Optionally may specify a node to return a cycle if it is in any
     *
     * @param string|int $node
     * @param boolean $firstOnly found only first cycle
     * @return array
     */
    public function findCycle($node = null, $firstOnly = true)
    {
        $nodes = (null === $node) ? $this->_nodes : array($node);
        $results = array();
        foreach ($nodes as $node) {
            $result = $this->dfs($node, $node);
            if ($result) {
                if ($firstOnly) {
                    return $result;
                } else {
                    $results[] = $result;
                }
            }
        }
        return $results;
    }

    /**
     * "Depth-first search" of a path between nodes
     *
     * Returns path as array of nodes or empty array if path does not exist.
     * Only first found path is returned. It will be not necessary the shortest or optimal in any way.
     *
     * @param string|int $fromNode
     * @param string|int $toNode
     * @param int $mode
     * @return array
     */
    public function dfs($fromNode, $toNode, $mode = self::DIRECTIONAL)
    {
        $this->_assertNode($fromNode, true);
        $this->_assertNode($toNode, true);
        return $this->_dfs($fromNode, $toNode, $this->getRelations($mode));
    }

    /**
     * Recursive sub-routine of dfs()
     *
     * @param string|int $fromNode
     * @param string|int $toNode
     * @param array $graph
     * @param array &$visited
     * @param array $stack
     * @return array
     * @link http://en.wikipedia.org/wiki/Depth-first_search
     */
    protected function _dfs($fromNode, $toNode, $graph, &$visited = array(), $stack = array())
    {
        $stack[] = $fromNode;
        $visited[$fromNode] = $fromNode;
        if (isset($graph[$fromNode][$toNode])) {
            $stack[] = $toNode;
            return $stack;
        }
        if (isset($graph[$fromNode])) {
            foreach ($graph[$fromNode] as $node) {
                if (!isset($visited[$node])) {
                    $result = $this->_dfs($node, $toNode, $graph, $visited, $stack);
                    if ($result) {
                        return $result;
                    }
                }
            }
        }
        return array();
    }

    /**
     * Verify existence or non-existence of a node
     *
     * @param string|int $node
     * @param bool $mustExist
     * @throws \InvalidArgumentException according to assertion rules
     */
    protected function _assertNode($node, $mustExist)
    {
        if (isset($this->_nodes[$node])) {
            if (!$mustExist) {
                throw new \InvalidArgumentException("Graph node '{$node}' already exists'.");
            }
        } else {
            if ($mustExist) {
                throw new \InvalidArgumentException("Graph node '{$node}' does not exist.");
            }
        }
    }
}
