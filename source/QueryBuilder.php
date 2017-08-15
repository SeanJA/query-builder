<?php

namespace SeanJA;

/**
 * A query builder class
 *
 * @author SeanJA <code@seanja.com>
 * @license MIT / GPLv2
 * @link http://github.com/SeanJA/query-builder
 * @property-read array $tables The TABLEs that are part of the query
 * @property-read array $columns The COLUMNs that are part of the query
 * @property-read array $wheres The WHERE conditions that are part of the query
 * @property-read array $joins The JOIN conditions that are part of the query
 * @property-read array $havings The HAVING conditions that are part of the query
 * @property-read array $groupBys The GROUP BY conditions that are part of the query
 * @property-read array $orderBys The ORDER BY conditions that are part of the query
 * @property-read array $deleteFroms The TABLES that will be deleted from in the query
 * @property-read int $limit The LIMIT condition that is part of the query
 * @property-read int $offset The OFFSET condition that is part of the query
 */
class QueryBuilder
{
    /**
     * Less than operator
     * @var string
     */
    const LESS_THAN = '<';
    /**
     * Less or equals than operator
     * @var string
     */
    const LESS_THAN_OR_EQUALS = '<=';
    /**
     * Greater than operator
     * @var string
     */
    const GREATER_THAN = '>';
    /**
     * Less than or equals operator
     * @var string
     */
    const GREATER_THAN_OR_EQUALS = '>=';
    /**
     * IS operator
     * @var string
     */
    const IS = 'IS';
    /**
     * IS NOT operator
     * @var string
     */
    const IS_NOT = 'IS NOT';
    /**
     * Equals operator
     * @var string
     */
    const EQUAL = '=';
    /**
     * Not equal operator
     * @var string
     */
    const NOT_EQUAL = '!=';
    /**
     * Like operator
     * @var string
     */
    const LIKE = 'LIKE';
    /**
     * Not Like operator
     * @var string
     */
    const NOT_LIKE = 'NOT LIKE';
    /**
     * The TABLEs that are part of the query
     * @var array
     */
    protected $tables = array();
    /**
     * The TABLEs that will be deleted from (if any are in this array)
     * @var array
     */
    protected $deleteFroms = array();
    /**
     * The COLUMNs that are part of the query
     * @var array
     */
    protected $columns = array();
    /**
     * The WHERE conditions that are part of the query
     * @var array
     */
    protected $wheres = array();
    /**
     * The JOIN conditions that are part of the query
     * @var array
     */
    protected $joins = array();
    /**
     * The HAVING conditions that are being used in this query
     * @var array
     */
    protected $havings = array();
    /**
     * The GROUP BY conditions that are part of the query
     * @var array
     */
    protected $groupBys = array();
    /**
     * The ORDER BY conditions that are part of the query
     * @var array
     */
    protected $orderBys = array();
    /**
     * The LIMIT condition that is part of the query
     * @var int
     */
    protected $limit;
    /**
     * The OFFSET condition that is part of the query
     * @var int
     */
    protected $offset;
    /**
     * The database object
     * @var DbInterface
     */
    private $dbInterface;

    /**
     *
     * @param DbInterface $dbInterface database class
     */
    public function __construct(&$dbInterface)
    {
        $this->dbInterface = $dbInterface;
    }

    /**
     * It is a get function, it returns things
     * @param string $var
     * @return mixed
     */
    public function __get($var)
    {
        return $this->$var;
    }

    /**
     * Add a table to the query
     * @param string $table the table being added
     * @param string $alias the alias of this table
     * @return QueryBuilder
     */
    public function table($table, $alias = null)
    {
        $this->tables[] = array(
            'table' => $table,
            'alias' => $alias
        );
        return $this;
    }

    /**
     * Add a column to the query
     * @param mixed $column the column being added
     * @param string $alias the alias of this table
     * @return QueryBuilder
     */
    public function column($column, $alias = null)
    {
        $column = $this->filterColumn($column);
        $this->columns[] = array(
            'column' => $column,
            'alias' => $alias
        );
        return $this;
    }

    /**
     * Add a where to the conditions, clears out the where stack
     * @param mixed $column The column being compared
     * @param mixed $where The value being compared to
     * @param string $comparison The comparison being done
     * @param string $comparison_type Whether it is an and or an OR
     * @param boolean $escape whether or not this value will be escaped
     * @return QueryBuilder
     */
    public function where($column, $where, $comparison = '=', $escape = true)
    {
        $this->clearWheres();
        $this->pushWhere($column, $where, $comparison, null, $escape);
        return $this;
    }

    /**
     * Add an 'AND' to the conditions
     * @param mixed $column The column being compared
     * @param mixed $value The value being compared to
     * @param string $comparison The comparison being done
     * @param boolean $escape whether or not this value will be escaped
     * @return QueryBuilder
     */
    public function andWhere($column, $value, $comparison = '=', $escape = true)
    {
        $this->pushWhere($column, $value, $comparison, 'AND', $escape);
        return $this;
    }

    /**
     * Add an 'AND' to the conditions
     * @param mixed $column The column being compared
     * @param mixed $value The value being compared to
     * @param string $comparison The comparison being done
     * @param boolean $escape whether or not this value will be escaped
     * @return QueryBuilder
     */
    public function orWhere($column, $value, $comparison = '=', $escape = true)
    {
        $this->pushWhere($column, $value, $comparison, 'OR', $escape);
        return $this;
    }

    /**
     * Add a join to the query
     * @param string $table
     * @param string $conditions
     * @return QueryBuilder
     */
    public function join($table, $conditions)
    {
        $this->pushJoin($table, $conditions, 'JOIN');
        return $this;
    }

    /**
     * Add a right join to the query
     * @param string $table
     * @param string $conditions
     * @return QueryBuilder
     */
    public function rightJoin($table, $conditions)
    {
        $this->pushJoin($table, $conditions, 'RIGHT JOIN');
        return $this;
    }

    /**
     * Add a left join to the query
     * @param string $table
     * @param string $conditions
     * @return QueryBuilder
     */
    public function leftJoin($table, $conditions)
    {
        $this->pushJoin($table, $conditions, 'LEFT JOIN');
        return $this;
    }

    /**
     * Add a straight join to the query
     * @param string $table
     * @param string $conditions
     * @return QueryBuilder
     */
    public function straightJoin($table, $conditions)
    {
        $this->pushJoin($table, $conditions, 'STRAIGHT JOIN');
        return $this;
    }

    /**
     * Add an inner join to the query
     * @param string $table
     * @param string $conditions
     * @return QueryBuilder
     */
    public function innerJoin($table, $conditions)
    {
        $this->pushJoin($table, $conditions, 'INNER JOIN');
        return $this;
    }

    /**
     * Add a cross join to the query
     * @param string $table
     * @param string $conditions
     * @return QueryBuilder
     */
    public function crossJoin($table, $conditions)
    {
        $this->pushJoin($table, $conditions, 'CROSS JOIN');
        return $this;
    }

    /**
     * Push an open bracket into the where stack to group OR conditions
     * @return QueryBuilder
     */
    public function beginOr()
    {
        $this->wheres[] = array(
            'bracket' => 'OPEN',
            'type' => 'OR'
        );
        return $this;
    }

    /**
     * Push an open bracket into the where stack to group AND conditions
     * @return QueryBuilder
     */
    public function beginAnd()
    {
        $this->wheres[] = array(
            'bracket' => 'OPEN',
            'type' => 'AND'
        );
        return $this;
    }

    /**
     * End an OR condition group
     * @return QueryBuilder
     */
    public function endOr()
    {
        $this->closedBracket();
        return $this;
    }

    /**
     * End an AND condition group
     * @return QueryBuilder
     */
    public function endAnd()
    {
        $this->closedBracket();
        return $this;
    }

    /**
     * Build a SELECT string for the current query
     * @return string
     */
    public function buildSelect()
    {
        $delete = 'SELECT ' . $this->buildColumnString()
            . ' FROM ' . $this->buildTableString()
            . $this->buildJoinString()
            . $this->buildWhereString()
            . $this->buildGroupByString()
            . $this->buildOrderByString()
            . $this->buildHavingString()
            . $this->buildLimitString()
            . $this->buildOffsetString();
        return $delete;
    }

    /**
     * Build a DELETE string from the current query
     * @return string
     */
    public function buildDelete()
    {
        $select = 'DELETE ' . $this->buildDeleteFromString()
            . 'FROM ' . $this->buildTableString()
            . $this->buildJoinString()
            . $this->buildWhereString()
            . $this->buildLimitString()
            . $this->buildOffsetString();
        return $select;
    }

    /**
     * Add a GROUP BY to the stack
     * @param string $filter The field to group by
     * @param string $order The order
     * @return QueryBuilder
     */
    public function groupBy($filter)
    {
        $this->groupBys[] = array(
            'filter' => $filter
        );
        return $this;
    }

    /**
     * Clear the GROUP BY stack
     * @return QueryBuilder
     */
    public function clearGroupBy()
    {
        $this->groupBys = array();
        return $this;
    }

    /**
     * Add an ORDER BY to the stack
     * @param string $column The column to order by
     * @param string $order The order to sort by
     * @return QueryBuilder
     */
    public function orderBy($column, $order = 'ASC')
    {
        $this->orderBys[] = array(
            'column' => $column,
            'order' => $order,
        );
        return $this;
    }

    /**
     * Clear the ORDER BY stack
     * @return QueryBuilder
     */
    public function clearOrderBy()
    {
        $this->orderBys = array();
        return $this;
    }

    /**
     * Add a having to the having stack, clears out the having stack
     * @param mixed $column The column being compared
     * @param mixed $having The value being compared to
     * @param string $comparison The comparison being done
     * @param string $comparison_type Whether it is an and or an OR
     * @param boolean $escape whether or not this value will be escaped
     * @return QueryBuilder
     */
    public function having($column, $having, $comparison = '=', $comparison_type = 'AND', $escape = true)
    {
        $this->clearHavings();
        $this->pushHaving($column, $having, $comparison, $comparison_type, $escape);
        return $this;
    }

    /**
     * Add a having to the having stack
     * @param mixed $column The column being compared
     * @param mixed $having The value being compared to
     * @param string $comparison The comparison being done
     * @param boolean $escape whether or not this value will be escaped
     * @return QueryBuilder
     */
    public function orHaving($column, $having, $comparison = '=', $escape = true)
    {
        $this->pushHaving($column, $having, $comparison, 'OR', $escape);
        return $this;
    }

    /**
     * Add a having to the having stack
     * @param mixed $column The column being compared
     * @param mixed $having The value being compared to
     * @param string $comparison The comparison being done
     * @param boolean $escape whether or not this value will be escaped
     * @return QueryBuilder
     */
    public function andHaving($column, $having, $comparison = '=', $escape = true)
    {
        $this->pushHaving($column, $having, $comparison, 'AND', $escape);
        return $this;
    }

    /**
     * Add a having to the conditions, clears out the where stack
     * @param mixed $column The column being compared
     * @param mixed $having The value being compared to
     * @param string $comparison The comparison being done
     * @param string $comparison_type Whether it is an and or an OR
     * @param boolean $escape whether or not this value will be escaped
     * @return QueryBuilder
     */
    public function clearHavings()
    {
        $this->havings = array();
        return $this;
    }

    /**
     * Add a having to the conditions, clears out the where stack
     * @param mixed $column The column being compared
     * @param mixed $having The value being compared to
     * @param string $comparison The comparison being done
     * @param string $comparison_type Whether it is an and or an OR
     * @param boolean $escape whether or not this value will be escaped
     */
    private function pushHaving($column, $having, $comparison = '=', $comparison_type = 'AND', $escape = true)
    {
        array_push($this->havings, array(
            'column' => $column,
            'having' => $having,
            'comparison' => $comparison,
            'comparison_type' => $comparison_type,
            'escape' => $escape
        ));
    }

    /**
     * Push a join onto the join stack
     * @param string $table
     * @param string $conditions
     * @param string $type
     */
    private function pushJoin($table, $conditions, $type)
    {
        array_push($this->joins, array(
            'table' => $table,
            'conditions' => $conditions,
            'type' => $type,
        ));
    }

    /**
     * Handle some special column value cases
     * @param mixed $column
     * @return string
     */
    private function filterColumn($column)
    {
        if ($column === null) {
            $column = 'NULL';
        } elseif ($column === true) {
            $column = 'TRUE';
        } elseif ($column === false) {
            $column = 'FALSE';
        }
        return $column;
    }

    /**
     * Push another value onto the where stack
     * @param mixed $column The column being compared
     * @param mixed $value The value being compared to
     * @param string $comparison The comparison being done
     * @param string $comparison_type Whether it is an AND or an OR
     * @param boolean $escape whether or not this value will be escaped
     */
    private function pushWhere($column, $value, $comparison, $comparison_type, $escape)
    {
        $comparison = strtoupper($comparison);
        $comparison_type = strtoupper($comparison_type);
        $column = $this->filterColumn($column);
        $old_value = $value;
        $value = $this->filterColumn($value);
        if ($old_value !== $value) {
            $escape = false;
        }
        array_push($this->wheres, array(
            'column' => $column,
            'value' => $value,
            'comparison' => $comparison,
            'type' => $comparison_type,
            'escape' => $escape,
        ));
    }

    /**
     * Clear the where stack
     * @return QueryBuilder
     */
    public function clearWheres()
    {
        $this->wheres = array();
        return $this;
    }

    /**
     * Set the LIMIT for the query
     * @param int $limit
     * @return QueryBuilder
     */
    public function setLimit($limit)
    {
        $new_limit = (int)$limit;
        if ($new_limit == $limit) {
            $this->limit = $new_limit;
        } else {
            $this->clearLimit();
        }
        return $this;
    }

    /**
     * Clear the LIMIT on the query
     * @return QueryBuilder
     */
    public function clearLimit()
    {
        $this->limit = null;
        return $this;
    }

    /**
     * Set the LIMIT for the query
     * @param int $offset
     * @return QueryBuilder
     */
    public function setOffset($offset)
    {
        $new_offset = (int)$offset;
        if ($new_offset == $offset) {
            $this->offset = $new_offset;
        } else {
            $this->clearOffset();
        }
        return $this;
    }

    /**
     * Clear the LIMIT on the query
     * @return QueryBuilder
     */
    public function clearOffset()
    {
        $this->offset = null;
        return $this;
    }

    /**
     * Add a TABLE to the list of TABLEs to delete from
     * @param type string
     * @return QueryBuilder
     */
    public function deleteFrom($delete_from)
    {
        $this->deleteFroms[] = $this->dbInterface->escape($delete_from);
        return $this;
    }

    /**
     * Build the COLUMN string part of the query
     * @return string
     */
    private function buildColumnString()
    {
        $columns = $this->buildAliasArray($this->columns, 'column');
        $string = implode(', ', $columns);
        if (!$string) {
            $string = '*';
        }
        return $string;
    }

    /**
     * Push a closing bracket into the where stack to end the grouping of conditions
     * @return QueryBuilder
     */
    private function closedBracket()
    {
        $this->wheres[] = array(
            'bracket' => 'CLOSE',
        );
    }

    /**
     * Build the TABLE string part of the query
     * @return string
     */
    private function buildTableString()
    {
        $tables = $this->buildAliasArray($this->tables, 'table');
        $string = implode(', ', $tables);
        return $string;
    }

    /**
     * Build an alias array out of the array
     * @param array $array
     * @param string $default the value that is always there
     * @return array
     */
    private function buildAliasArray(array $array, $default)
    {
        $return = array();
        foreach ($array as $a) {
            $temp = $a[$default];
            if ($a['alias']) {
                $temp .= ' AS ' . $a['alias'];
            }
            $return[] = $temp;
        }
        return $return;
    }

    /**
     * Build the WHERE string part of the query
     * @return string
     */
    private function buildWhereString()
    {
        $first = true;
        $bracket = false;
        $string = '';
        foreach ($this->wheres as $w) {
            if ($first) {
                $string = ' WHERE ';
                $first = false;
            } else {
                if (!$bracket && !isset($w['bracket'])) {
                    $string .= ' ' . $w['type'] . ' ';
                } else {
                    $bracket = false;
                }
            }
            if (isset($w['bracket'])) {
                if ($w['bracket'] === 'OPEN') {
                    $string .= '( ';
                    $bracket = true;
                } else {
                    $string .= ' )';
                }
            } else {
                $string .= $w['column'] . ' ' . $w['comparison'] . ' ';
                if ($w['escape']) {
                    $string .= $this->dbInterface->quote($this->dbInterface->escape($w['value']));
                } else {
                    $string .= $w['value'];
                }
            }
        }
        return $string;
    }

    /**
     * Build the JOIN string part of the query
     * @return string
     */
    private function buildJoinString()
    {
        $string = '';
        foreach ($this->joins as $j) {
            $string .= ' ' . $j['type'] . ' ' . $j['table'] . ' ON (' . $j['conditions'] . ')';
        }
        return $string;
    }

    /**
     * Build the having part of the query
     * @return string
     */
    private function buildHavingString()
    {
        $string = '';
        if (!empty($this->havings)) {
            $tmp = array_shift($this->havings);
            $string .= ' HAVING ' . $tmp['column'] . ' ' . $tmp['comparison'] . ' ' .
                $this->dbInterface->quote($this->dbInterface->escape($tmp['having']));
        }
        foreach ($this->havings as $h) {
            $string .= ' ' . $h['comparison_type'] . ' ' . $h['column'] . ' ' . $h['comparison'] . ' ' .
                $this->dbInterface->quote($this->dbInterface->escape($h['having']));
        }
        return $string;
    }

    /**
     * Build the GROUP BY part of the query
     * @return $string
     */
    private function buildGroupByString()
    {
        $string = '';
        if (!empty($this->groupBys)) {
            $string .= ' GROUP BY ';
            $tmp = array();
            foreach ($this->groupBys as $g) {
                $tmp[] = $g['filter'];
            }
            $string .= implode(', ', $tmp);
        }
        return $string;
    }

    /**
     * Build the ORDER BY part of the query
     * @return $string
     */
    private function buildOrderByString()
    {
        $string = '';
        if (!empty($this->orderBys)) {
            $string .= ' ORDER BY ';
            $tmp = array();
            foreach ($this->orderBys as $o) {
                $tmp[] = $o['column'] . ' ' . $o['order'];
            }
            $string .= implode(', ', $tmp);
        }
        return $string;
    }

    /**
     * Build the LIMIT part of the query
     * @return string
     */
    private function buildLimitString()
    {
        $string = '';
        if ($this->limit) {
            $string .= ' LIMIT ' . $this->limit;
        }
        return $string;
    }

    /**
     * Build the OFFSET part of the query
     * @return string
     */
    private function buildOffsetString()
    {
        $string = '';
        if ($this->offset) {
            $string .= ' OFFSET ' . $this->offset;
        }
        return $string;
    }

    /**
     * List the tables to delete from
     * @return string
     */
    private function buildDeleteFromString()
    {
        $string = '';
        if ($this->deleteFroms) {
            $string .= implode(',', $this->deleteFroms) . ' ';
        }
        return $string;
    }
}
