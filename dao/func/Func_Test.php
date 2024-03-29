<?php
namespace ITRocks\Framework\Dao\Func;

use Exception;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Sql\Builder\Select;
use ITRocks\Framework\Tests\Objects\Client;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools;

/**
 * Dao functions unit tests
 *
 * @group functional
 */
class Func_Test extends Test
{

	//------------------------------------------------------------------------------ testConcatSelect
	public function testConcatSelect() : void
	{
		$builder = new Select(
			Order::class,
			['string_concat' => new Concat(['number', 'date'])]
		);
		self::assertEquals(
			'SELECT CONCAT(t0.`number`, " ", t0.`date`) AS `string_concat`' . LF
				. 'FROM `test_orders` t0',
			$builder->buildQuery()
		);
	}

	//--------------------------------------------------------------------------------- testCondition
	public function testCondition() : void
	{
		$search  = (new Tools\Search_Array_Builder())->build('client.number', 'XXXX');
		$builder = new Select(
			Order::class,
			[
				'case_result'      => new Condition($search, 'client.name', 'string_client_unknown'),
				'case_result_func' => new Condition($search, new Concat(['number', 'client.number'])),
			]
		);
		self::assertEquals(
			'SELECT '
				. 'CASE WHEN t1.`number` = "XXXX" THEN t1.`name` ELSE "string_client_unknown" END '
				. 'AS `case_result`, '
				. 'CASE WHEN t1.`number` = "XXXX" THEN CONCAT(t0.`number`, " ", t1.`number`) END '
				. 'AS `case_result_func`' . LF
				. 'FROM `test_orders` t0' . LF
				. 'INNER JOIN `test_clients` t1 ON t1.id = t0.id_client',
			$builder->buildQuery()
		);
	}

	//--------------------------------------------------------------------------------------- testDay
	public function testDay() : void
	{
		$builder = new Select(
			Order::class,
			['date' => Func::day()]
		);
		self::assertEquals(
			'SELECT DAY(t0.`date`) AS `date`' . LF . 'FROM `test_orders` t0',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------------- testDayInWhere
	public function testDayInWhere() : void
	{
		$builder = new Select(
			Order::class,
			['number'],
			[Func::day('date') => 11]
		);
		self::assertEquals(
			'SELECT t0.`number`' . LF . 'FROM `test_orders` t0' . LF . 'WHERE DAY(t0.`date`) = 11',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------------- testGroupConcat
	public function testGroupConcat() : void
	{
		$builder = new Select(
			Client::class,
			[
				'number'                 => new Group_Concat(),
				'name'                   => new Group_Concat(';'),
				'group_concat_with_func' => new Group_Concat(new Concat(['number', 'name'])),
			]
		);
		self::assertEquals(
			'SELECT GROUP_CONCAT(DISTINCT t0.`number` ORDER BY t0.`number`) AS `number`, '
				. 'GROUP_CONCAT(DISTINCT t0.`name` ORDER BY t0.`name` SEPARATOR ";") AS `name`, '
				. 'GROUP_CONCAT(DISTINCT CONCAT(t0.`number`, " ", t0.`name`)'
				. ' ORDER BY CONCAT(t0.`number`, " ", t0.`name`)) AS `group_concat_with_func`' . LF
				. 'FROM `test_clients` t0',
			$builder->buildQuery()
		);
	}

	//---------------------------------------------------------------------------------- testInSelect
	public function testInSelect() : void
	{
		$sub_select = new Select(
			Order::class,
			['date']
		);
		$builder    = new Select(
			Order::class,
			null,
			['date' => Func::inSelect($sub_select)]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE t0.`date` IN ('
				. 'SELECT t0.`date`' . LF
				. 'FROM `test_orders` t0)',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------------- testIsGreatest
	public function testIsGreatest() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['date' => Func::isGreatest(['number'])]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF . 'INNER JOIN ('
				. 'SELECT t0.`number`, MAX(t0.`date`) AS `date`' . LF
				. 'FROM `test_orders` t0' . LF
				. 'GROUP BY t0.`number`'
				. ') t1'
				. ' ON t1.`number` = t0.`number` AND t1.`date` = t0.`date`',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------------------- testLeft
	public function testLeft() : void
	{
		$builder = new Select(
			Order::class,
			['number' => Func::left(4)]
		);
		self::assertEquals(
			'SELECT LEFT(t0.`number`, 4) AS `number`' . LF . 'FROM `test_orders` t0',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------------- testLeftInWhere
	public function testLeftInWhere() : void
	{
		$builder = new Select(
			Order::class,
			['number'],
			[Func::left('number', 3) => '123']
		);
		self::assertEquals(
			'SELECT t0.`number`' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE LEFT(t0.`number`, 3) = "123"',
			$builder->buildQuery()
		);
	}

	//--------------------------------------------------------------------------------- testLeftMatch
	public function testLeftMatch() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::leftMatch('N01181355010')]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE t0.`number` = LEFT("N01181355010", LENGTH(t0.`number`))',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------------- testLogicalAnd
	public function testLogicalAnd() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::andOp([_TRUE, _FALSE])]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE (t0.`number` = "true" AND t0.`number` = "false")',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------- testLogicalAndNegated
	public function testLogicalAndNegated()
	{
		$argument = Func::andOp([_TRUE, _FALSE]);
		$argument->negate();
		$builder = new Select(
			Order::class,
			null,
			['number' => $argument]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE (NOT (t0.`number` = "true") OR NOT (t0.`number` = "false"))',
			$builder->buildQuery()
		);
	}

	//-------------------------------- testLogicalExceptionRaisedOnNotOrTrueOperatorWithArrayArgument
	public function testLogicalExceptionRaisedOnNotOrTrueOperatorWithArrayArgument() : void
	{
		$check = false;
		try {
			$argument = new Logical(Logical::TRUE_OPERATOR, [_TRUE, _FALSE]);
			unset($argument);
		}
		catch (Exception) {
			$check = true;
		}
		self::assertTrue($check);
	}

	//-------------------------------------------------------------------------------- testLogicalNot
	public function testLogicalNot() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::notOp(_TRUE)]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE NOT (t0.`number` = "true")',
			$builder->buildQuery()
		);
	}

	//----------------------------------------------------------------------------- testLogicalNotAnd
	public function testLogicalNotAnd() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::notOp(Func::andOp([_TRUE, _FALSE]))]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE NOT ((t0.`number` = "true" AND t0.`number` = "false"))',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------- testLogicalNotNegated
	public function testLogicalNotNegated() : void
	{
		$argument = Func::notOp(_TRUE);
		$argument->negate();
		$builder = new Select(
			Order::class,
			null,
			['number' => $argument]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE (t0.`number` = "true")',
			$builder->buildQuery()
		);
	}

	//----------------------------------------------------------------------------- testLogicalNotNot
	public function testLogicalNotNot() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::notOp(Func::notOp(_TRUE))]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE NOT (NOT (t0.`number` = "true"))',
			$builder->buildQuery()
		);
	}

	//--------------------------------------------------------------------------------- testLogicalOr
	public function testLogicalOr() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::orOp([_TRUE, _FALSE])]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE (t0.`number` = "true" OR t0.`number` = "false")',
			$builder->buildQuery()
		);
	}

	//---------------------------------------------------------------------------- testLogicalOrInAnd
	public function testLogicalOrInAnd() : void
	{
		$builder = new Select(
			Order::class,
			null,
			[
				'number' => Func::orOp([
					Func::orOp([_TRUE, _FALSE]),
					Func::orOp([_TRUE, _FALSE]),
				])
			]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE ((t0.`number` = "true" OR t0.`number` = "false")'
				. ' OR (t0.`number` = "true" OR t0.`number` = "false"))',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------ testLogicalTrueShouldNotBeCalledDirectly
	/**
	 * @throws Exception
	 */
	public function testLogicalTrueShouldNotBeCalledDirectly() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => new Logical(Logical::TRUE_OPERATOR, _TRUE)]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE (t0.`number` = "true")',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------------- testLogicalXor
	public function testLogicalXor() : void
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::xorOp([_TRUE, _FALSE])]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE (t0.`number` = "true" XOR t0.`number` = "false")',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------- testLogicalXorNegated
	public function testLogicalXorNegated() : void
	{
		$argument = Func::xorOp([_TRUE, _FALSE]);
		$argument->negate();
		$builder = new Select(
			Order::class,
			null,
			['number' => $argument]
		);
		self::assertEquals(
			'SELECT t0.*' . LF
				. 'FROM `test_orders` t0' . LF
				. 'WHERE NOT ((t0.`number` = "true" XOR t0.`number` = "false"))',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------------------- testMonth
	public function testMonth() : void
	{
		$builder = new Select(
			Order::class,
			['date' => Func::month()]
		);
		self::assertEquals(
			'SELECT MONTH(t0.`date`) AS `date`' . LF . 'FROM `test_orders` t0',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------------ testMonthInWhere
	public function testMonthInWhere() : void
	{
		$builder = new Select(
			Order::class,
			['number'],
			[Func::month('date') => 11]
		);
		self::assertEquals(
			'SELECT t0.`number`' . LF . 'FROM `test_orders` t0' . LF . 'WHERE MONTH(t0.`date`) = 11',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------------------- testYear
	public function testYear() : void
	{
		$builder = new Select(
			Order::class,
			['date' => Func::year()]
		);
		self::assertEquals(
			'SELECT YEAR(t0.`date`) AS `date`' . LF . 'FROM `test_orders` t0',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------------- testYearInWhere
	public function testYearInWhere() : void
	{
		$builder = new Select(
			Order::class,
			['number'],
			[Func::year('date') => 11]
		);
		self::assertEquals(
			'SELECT t0.`number`' . LF . 'FROM `test_orders` t0' . LF . 'WHERE YEAR(t0.`date`) = 11',
			$builder->buildQuery()
		);
	}

}
