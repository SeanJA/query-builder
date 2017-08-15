<?php
namespace Tests\SeanJA;
require_once __DIR__.'/../bootstrap.php';

use PHPUnit_Framework_TestCase;
use SeanJA\QueryBuilder;

class QueryTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var QueryBuilder
	 */
	protected $q;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$db = new Db();
		$this->q = new QueryBuilder($db);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		$this->q = null;
	}
	/**
	 * Test adding a single table to the query
	 */
	public function testAddTable(){
		$this->q->table('test');
		$this->assertEquals(array('table'=>'test', 'alias'=>null), $this->q->tables[0]);
	}
	public function testAddColumn(){
		$this->q->column('test');
		$expected = array(
			'column'=>'test',
			'alias'=>null
		);
		$this->assertEquals($expected, $this->q->columns[0]);
	}
	public function testNULLColumn(){
		$this->q->column(null);
		$expected = array(
			'column'=>'NULL',
			'alias'=>null
		);
		$this->assertEquals($expected, $this->q->columns[0]);
	}
	public function testFALSEColumn(){
		$this->q->column(false);
		$expected = array(
			'column'=>'FALSE',
			'alias'=>null
		);
		$this->assertEquals($expected, $this->q->columns[0]);
	}
	public function testTRUEColumn(){
		$this->q->column(true);
		$expected = array(
			'column'=>'TRUE',
			'alias'=>null
		);
		$this->assertEquals($expected, $this->q->columns[0]);
	}
	public function testMathColumn(){
		$this->q->column('1 = 1');
		$expected = array(
			'column'=>'1 = 1',
			'alias'=>null
		);
		$this->assertEquals($expected, $this->q->columns[0]);
	}
	public function testFunctionColumn(){
		$this->q->column('COUNT(*)');
		$expected = array(
			'column'=>'COUNT(*)',
			'alias'=>null
		);
		$this->assertEquals($expected, $this->q->columns[0]);
	}
	/**
	 * Test chaining of functions
	 */
	public function testChaining(){
		$this->q->table('test')
				->column('test_2', 'test')
				->table('test_3', 'test_2');
		
		$expected_tables = array(
			array(
				'table'=>'test',
				'alias'=>null
			),
			array(
				'table'=>'test_3',
				'alias'=>'test_2'
			)
		);
		$expected_columns = array(
			array(
				'column'=>'test_2',
				'alias'=>'test'
			)
		);
		$this->assertEquals($expected_tables, $this->q->tables);
		$this->assertEquals($expected_columns, $this->q->columns);
	}
	public function testFirstWhere(){
		$this->q->where('1', '1');
		$expected = array(
			'column'=>'1',
			'value'=>'1',
			'comparison'=>'=',
			'type'=>null,
			'escape'=>true,
		);
		$this->assertEquals($expected, $this->q->wheres[0]);
	}
	public function testClearWhere(){
		$this->q->where('1', '1')->where('2', '2');
		$expected = array(
			'column'=>'2',
			'value'=>'2',
			'comparison'=>'=',
			'type'=>null,
			'escape'=>true,
		);
		$this->assertEquals($expected, $this->q->wheres[0]);
	}
	public function testAndWhere(){
		$this->q->where('1', '1')->andWhere('2', '2');
		$expected =  array(
			array(
				'column'=>'1',
				'value'=>'1',
				'comparison'=>'=',
				'type'=>null,
				'escape'=>true,
			),
			array(
				'column'=>'2',
				'value'=>'2',
				'comparison'=>'=',
				'type'=>'AND',
				'escape'=>true,
			)
		);
		$this->assertEquals($expected, $this->q->wheres);
	}
	public function testOrWhere(){
		$this->q->where('1', '1')->orWhere('2', '2');
		$expected =  array(
			array(
				'column'=>'1',
				'value'=>'1',
				'comparison'=>'=',
				'type'=>null,
				'escape'=>true,
			),
			array(
				'column'=>'2',
				'value'=>'2',
				'comparison'=>'=',
				'type'=>'OR',
				'escape'=>true,
			)
		);
		$this->assertEquals($expected, $this->q->wheres);
	}
	public function testAndWhereOrWhere(){
		$this->q->where('1', '1')
				->andWhere(true, true)
				->andWhere(null, null, 'iS')
				->orWhere('2', '2', '=', false);
		$expected =  array(
			array(
				'column'=>'1',
				'value'=>'1',
				'comparison'=>'=',
				'type'=>null,
				'escape'=>true,
			),
			array(
				'column'=>'TRUE',
				'value'=>'TRUE',
				'comparison'=>'=',
				'type'=>'AND',
				'escape'=>false,
			),
			array(
				'column'=>'NULL',
				'value'=>'NULL',
				'comparison'=>'IS',
				'type'=>'AND',
				'escape'=>false,
			),
			array(
				'column'=>'2',
				'value'=>'2',
				'comparison'=>'=',
				'type'=>'OR',
				'escape'=>false,
			)
		);
		$this->assertEquals($expected, $this->q->wheres);
	}
	public function testBrackets(){
		$this->q->where('1', '1')
				->beginAnd()
				->andWhere(true, true)
				->beginOr()
				->andWhere(null, null, 'iS')
				->endOr()
				->endAnd()
				->orWhere('2', '2', '=', false);
		$expected =  array(
			array(
				'column'=>'1',
				'value'=>'1',
				'comparison'=>'=',
				'type'=>null,
				'escape'=>true,
			),
			array(
				'bracket'=>'OPEN',
				'type'=>'AND'
			),
			array(
				'column'=>'TRUE',
				'value'=>'TRUE',
				'comparison'=>'=',
				'type'=>'AND',
				'escape'=>false,
			),
			array(
				'bracket'=>'OPEN',
				'type'=>'OR'
			),
			array(
				'column'=>'NULL',
				'value'=>'NULL',
				'comparison'=>'IS',
				'type'=>'AND',
				'escape'=>false,
			),
			array(
				'bracket'=>'CLOSE',
			),
			array(
				'bracket'=>'CLOSE',
			),
			array(
				'column'=>'2',
				'value'=>'2',
				'comparison'=>'=',
				'type'=>'OR',
				'escape'=>false,
			)
		);
		$this->assertEquals($expected, $this->q->wheres);
	}
	public function testBuildSelect(){
		$this->q->column('column')
				->column('column')
				->table('table')
				->where(true, true);
		$expected = 'SELECT column, column FROM table WHERE TRUE = TRUE';
		$this->assertEquals($expected,$this->q->buildSelect());
	}
	public function testBuildSelectWithTableAlias(){
		$this->q->column('column', 'col')
				->column('column', 'pink')
				->table('table', 'Table')
				->where(true, 1);
		$expected = "SELECT column AS col, column AS pink FROM table AS Table WHERE TRUE = '1'";
		$this->assertEquals($expected,$this->q->buildSelect());
	}
	public function testSelectStar(){
		$this->q->table('table', 'Table')
				->where(true, 'true');
		$expected = "SELECT * FROM table AS Table WHERE TRUE = 'true'";
		$this->assertEquals($expected,$this->q->buildSelect());
	}
	public function testWhereGroup(){
		$this->q->table('table', 'Table')
				->beginAnd()
				->andWhere(true, 'true')
				->endAnd();
		$expected = "SELECT * FROM table AS Table WHERE ( TRUE = 'true' )";
		$this->assertEquals($expected,$this->q->buildSelect());
	}
	public function testMultiWhereGroup(){
		$this->q->table('table', 'Table')
				->beginAnd()
				->andWhere(true, 'true')
				->orWhere('2', false)
				->endAnd();
		$expected = "SELECT * FROM table AS Table WHERE ( TRUE = 'true' OR 2 = FALSE )";
		$this->assertEquals($expected,$this->q->buildSelect());
	}

	public function testMultiWhere(){
		$this->q->table('table')
				->beginAnd()
				->andWhere('col_1', 1)
				->orWhere('col_2', 2)
				->endAnd()
				->orWhere('col_3', 3, '!=');
		$expected = "SELECT * FROM table WHERE ( col_1 = '1' OR col_2 = '2' ) OR col_3 != '3'";
		$this->assertEquals($expected,$this->q->buildSelect());
	}

	public function testMultiWhereGrouping(){
		$this->q->table('table')
				->beginAnd()
				->beginAnd()
				->andWhere('col_1', 1)
				->orWhere('col_2', 2)
				->endAnd()
				->endAnd()
				->orWhere('col_3', 3, '!=');
		$expected = "SELECT * FROM table WHERE ( ( col_1 = '1' OR col_2 = '2' ) ) OR col_3 != '3'";
		$this->assertEquals($expected,$this->q->buildSelect());
	}

	public function testJoin(){
		$this->q->table('table')
				->join('table_2', 'table.id = table_2.id');
		$expected = array(
			'table'=>'table_2',
			'conditions'=>'table.id = table_2.id',
			'type'=>'JOIN',
		);
		$this->assertEquals($expected, $this->q->joins[0]);
	}

	public function testRightJoin(){
		$this->q->table('table')
				->rightJoin('table_2', 'table.id = table_2.id');
		$expected = array(
			'table'=>'table_2',
			'conditions'=>'table.id = table_2.id',
			'type'=>'RIGHT JOIN',
		);
		$this->assertEquals($expected, $this->q->joins[0]);
	}

	public function testLeftJoin(){
		$this->q->table('table')
				->leftJoin('table_2', 'table.id = table_2.id');
		$expected = array(
			'table'=>'table_2',
			'conditions'=>'table.id = table_2.id',
			'type'=>'LEFT JOIN',
		);
		$this->assertEquals($expected, $this->q->joins[0]);
	}

	public function testStraifghtJoin(){
		$this->q->table('table')
				->straightJoin('table_2', 'table.id = table_2.id');
		$expected = array(
			'table'=>'table_2',
			'conditions'=>'table.id = table_2.id',
			'type'=>'STRAIGHT JOIN',
		);
		$this->assertEquals($expected, $this->q->joins[0]);
	}

	public function testInnerJoin(){
		$this->q->table('table')
				->innerJoin('table_2', 'table.id = table_2.id');
		$expected = array(
			'table'=>'table_2',
			'conditions'=>'table.id = table_2.id',
			'type'=>'INNER JOIN',
		);
		$this->assertEquals($expected, $this->q->joins[0]);
	}

	public function testCrossJoin(){
		$this->q->table('table')
				->crossJoin('table_2', 'table.id = table_2.id');
		$expected = array(
			'table'=>'table_2',
			'conditions'=>'table.id = table_2.id',
			'type'=>'CROSS JOIN',
		);
		$this->assertEquals($expected, $this->q->joins[0]);
	}

	public function testMultipleJoinOrder(){
		$this->q->table('table')
				->join('join_table', 'id')
				->rightJoin('right_table', 'id')
				->leftJoin('left_table', 'id')
				->innerJoin('inner_table', 'id')
				->straightJoin('straight_table', 'id')
				->crossJoin('cross_table', 'id');

		//make sure there are 6 joins
		$this->assertEquals(6, count($this->q->joins));

		//make sure they are in the right order
		$this->assertEquals('JOIN', $this->q->joins[0]['type']);
		$this->assertEquals('RIGHT JOIN', $this->q->joins[1]['type']);
		$this->assertEquals('LEFT JOIN', $this->q->joins[2]['type']);
		$this->assertEquals('INNER JOIN', $this->q->joins[3]['type']);
		$this->assertEquals('STRAIGHT JOIN', $this->q->joins[4]['type']);
		$this->assertEquals('CROSS JOIN', $this->q->joins[5]['type']);
	}

	public function testJoinQuery(){
		$this->q->table('table')
				->join('join_table', 'id');
		$expected = 'SELECT * FROM table JOIN join_table ON (id)';
		$this->assertEquals($expected, $this->q->buildSelect());
	}

	public function testTwoJoinQuery(){
		$this->q->table('table')
				->column('table.column')
				->join('join_table', 'id')
				->rightJoin('right_table', 'id2')
				->where('column', '1', '!=');
		$expected = "SELECT table.column FROM table JOIN join_table ON (id) RIGHT JOIN right_table ON (id2) WHERE column != '1'";
		$this->assertEquals($expected, $this->q->buildSelect());
	}

	public function testOneGroupBy(){
		$this->q->table('table')
				->groupBy('test_1');
		$expected = array('filter'=>'test_1');
		$this->assertEquals($expected, $this->q->groupBys[0]);
		$sql_expected = 'SELECT * FROM table GROUP BY test_1';
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testMultipleGroupBy(){
		$this->q->table('table')
				->groupBy('test_1')
				->groupBy('test_2');
		$expected = array(
			array(
				'filter'=>'test_1'
			),
			array(
				'filter'=>'test_2'
			),
		);
		$this->assertEquals($expected, $this->q->groupBys);
		$sql_expected = 'SELECT * FROM table GROUP BY test_1, test_2';
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testClearGroupBy(){
		$this->q->table('table')
				->groupBy('test_1')
				->groupBy('test_2')
				->clearGroupBy();
		$expected = array();
		$this->assertEquals($expected, $this->q->groupBys);
		$sql_expected = 'SELECT * FROM table';
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testOneOrderBy(){
		$this->q->table('table')
				->orderBy('test_1');
		$expected = array(
			'column'=>'test_1',
			'order'=>'ASC'
		);
		$this->assertEquals($expected, $this->q->orderBys[0]);
		$sql_expected = 'SELECT * FROM table ORDER BY test_1 ASC';
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testMultipleOrderBy(){
		$this->q->table('table')
				->orderBy('test_1')
				->orderBy('test_2', 'DESC');
		$expected = array(
			array(
				'column'=>'test_1',
				'order'=>'ASC'
			),
			array(
				'column'=>'test_2',
				'order'=>'DESC'
			),
		);
		$this->assertEquals($expected, $this->q->orderBys);
		$sql_expected = 'SELECT * FROM table ORDER BY test_1 ASC, test_2 DESC';
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testClearOrderBy(){
		$this->q->table('table')
				->orderBy('test_1')
				->orderBy('test_2', 'DESC')
				->clearOrderBy();
		$expected = array();
		$this->assertEquals($expected, $this->q->orderBys);
		$sql_expected = 'SELECT * FROM table';
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testOneHaving(){
		$this->q->table('table')
				->having('test_1', 'test_2');
		$expected = array(
			'column'=>'test_1',
			'having'=>'test_2',
			'comparison'=>'=',
			'comparison_type'=>'AND',
			'escape'=>TRUE
		);
		$this->assertEquals($expected, $this->q->havings[0]);

		$sql_expected = "SELECT * FROM table HAVING test_1 = 'test_2'";
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testMultipleHaving(){
		$this->q->table('table')
				->having('test_1', 'test_2')
				->andHaving('test_3', 'test_4', '<>')
				->orHaving('test_5', 'test_6', 'IS NOT');
		$expected = array(
			array(
				'column'=>'test_1',
				'having'=>'test_2',
				'comparison'=>'=',
				'comparison_type'=>'AND',
				'escape'=>TRUE
			),
			array(
				'column'=>'test_3',
				'having'=>'test_4',
				'comparison'=>'<>',
				'comparison_type'=>'AND',
				'escape'=>TRUE
			),
			array(
				'column'=>'test_5',
				'having'=>'test_6',
				'comparison'=>'IS NOT',
				'comparison_type'=>'OR',
				'escape'=>TRUE
			),
		);
		$this->assertEquals($expected, $this->q->havings);

		$sql_expected = "SELECT * FROM table HAVING test_1 = 'test_2' AND test_3 <> 'test_4' OR test_5 IS NOT 'test_6'";
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testLimit(){
		$this->q->table('table')
				->setLimit(1);
		$expected = 1;
		$this->assertEquals($expected, $this->q->limit);

		$this->q->setLimit(2);
		$expected = 2;
		$this->assertEquals($expected, $this->q->limit);

		$this->q->setLimit('2.4');
		$expected = null;
		$this->assertEquals($expected, $this->q->limit);

		$this->q->clearLimit();
		$expected = null;
		$this->assertEquals($expected, $this->q->limit);

		$this->q->setLimit(40);

		$sql_expected = "SELECT * FROM table LIMIT 40";
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}

	public function testOffset(){
		$this->q->table('table')
				->setOffset(1);
		$expected = 1;
		$this->assertEquals($expected, $this->q->offset);

		$this->q->setOffset(2);
		$expected = 2;
		$this->assertEquals($expected, $this->q->offset);

		$this->q->setOffset('2.4');
		$expected = null;
		$this->assertEquals($expected, $this->q->offset);

		$this->q->clearOffset();
		$expected = null;
		$this->assertEquals($expected, $this->q->offset);

		$this->q->setOffset(40);

		$sql_expected = "SELECT * FROM table OFFSET 40";
		$this->assertEquals($sql_expected,$this->q->buildSelect());
	}
	
	public function testDelete(){
		$expected = "DELETE FROM test WHERE t1 = 'foo' AND t2 = 'foo_2'";
		$this->q->table('test')
				->where('t1', 'foo')
				->andWhere('t2', 'foo_2');
		$this->assertEquals($expected, $this->q->buildDelete());
	}
	
	public function testDeleteDuplicateEntries(){
		//http://dev.mysql.com/doc/refman/5.0/en/delete.html#c5206 (deleting duplicate entries example)
		$expected = 'DELETE t1 FROM tbl_name AS t1, tbl_name AS t2 WHERE t1.userID = t2.userID AND t1.eventID = t2.eventID AND t1.ueventID < t2.ueventID';
		$this->q->deleteFrom('t1')
				->table('tbl_name', 't1')
				->table('tbl_name', 't2')
				->where('t1.userID', 't2.userID', QueryBuilder::EQUAL, false)
				->andWhere('t1.eventID', 't2.eventID', QueryBuilder::EQUAL, false)
				->andWhere('t1.ueventID', 't2.ueventID',QueryBuilder::LESS_THAN, false);
		$this->assertEquals($expected, $this->q->buildDelete());
	}
	
	public function testDeleteSelect(){
		$expected_delete = "DELETE FROM test WHERE t1 = 'foo' AND t2 = 'foo_2'";
		$expected_select = "SELECT * FROM test WHERE t1 = 'foo' AND t2 = 'foo_2'";
		$this->q->table('test')
				->where('t1', 'foo')
				->andWhere('t2', 'foo_2');
		$this->assertEquals($expected_delete, $this->q->buildDelete());
		$this->assertEquals($expected_select, $this->q->buildSelect());
	}
}