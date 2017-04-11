<?php
namespace ITRocks\Framework\Dao\Func;

use Exception;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Sql\Builder\Select;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tools;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Default_List_Data;

/**
 * Dao functions unit tests
 */
class Tests extends Test
{

	//---------------------------------------------------------------------------- testCaseExpression
	public function testCaseExpression()
	{
		$search  = (new Tools\Search_Array_Builder())->build('client.number', 'XXXX');
		$builder = new Select(
			Order::class,
			[
				'case_result' => new Case_Expression($search, 'client.name', 'client_unknown'),
				'case_result_func' => new Case_Expression($search, new Concat(['number', 'client.number'])),
			]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT '
			. 'CASE WHEN t1.`number` = "XXXX" THEN "client.name" ELSE "client_unknown" END '
			. 'AS `case_result`, '
			. 'CASE WHEN t1.`number` = "XXXX" THEN CONCAT(t0.`number`, " ", t1.`number`) END '
			. 'AS `case_result_func`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `clients` t1 ON t1.id = t0.id_client'
		);
	}

	//--------------------------------------------------------------------------- testConcatDaoSelect
	public function testConcatDaoSelect()
	{
		$class_name = Order::class;
		$properties = ['string_concat' => new Concat(['number', 'date'])];
		$this->assume(
			__METHOD__,
			Dao::select($class_name, $properties),
			new Default_List_Data($class_name, $properties)
		);
	}

	//------------------------------------------------------------------------------ testConcatSelect
	public function testConcatSelect()
	{
		$builder = new Select(
			Order::class,
			['string_concat' => new Concat(['number', 'date'])]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT CONCAT(t0.`number`, " ", t0.`date`) AS `string_concat`' . LF
			. 'FROM `orders` t0'
		);
	}

	//---------------------------------------------------------------------------------- testInSelect
	public function testInSelect()
	{
		$sub_select = new Select(
			Order::class,
			['date']
		);
		$builder = new Select(
			Order::class,
			null,
			['date' => Func::inSelect($sub_select)]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE t0.`date` IN ('
			. 'SELECT t0.`date`' . LF
			. 'FROM `orders` t0)'
		);
	}

	//-------------------------------------------------------------------------------- testIsGreatest
	public function testIsGreatest()
	{
		$builder = new Select(
			Order::class,
			null,
			['date' => Func::isGreatest(['number'])]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF . 'INNER JOIN ('
			. 'SELECT t0.`number`, MAX(t0.`date`) AS `date`' . LF
			. 'FROM `orders` t0' . LF
			. 'GROUP BY t0.`number`'
			. ') t1'
			. ' ON t1.`number` = t0.`number` AND t1.`date` = t0.`date`'
		);
	}

	//-------------------------------------------------------------------------------------- testLeft
	public function testLeft()
	{
		$builder = new Select(
			Order::class,
			['number' => Func::left(4)]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT LEFT(t0.`number`, 4) AS `number`' . LF . 'FROM `orders` t0'
		);
	}

	//--------------------------------------------------------------------------------- testLeftMatch
	public function testLeftMatch()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::leftMatch('N01181355010')]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE t0.`number` = LEFT("N01181355010", LENGTH(t0.`number`))'
		);
	}

	//-------------------------------------------------------------------------------- testLogicalAnd
	public function testLogicalAnd()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::andOp([_TRUE, _FALSE])]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE (t0.`number` = "true" AND t0.`number` = "false")'
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
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE (NOT (t0.`number` = "true") OR NOT (t0.`number` = "false"))'
		);
	}

	//-------------------------------- testLogicalExceptionRaisedOnNotOrTrueOperatorWithArrayArgument
	public function testLogicalExceptionRaisedOnNotOrTrueOperatorWithArrayArgument()
	{
		$check = false;
		try
		{
			$argument = new Logical(Logical::TRUE_OPERATOR, [_TRUE, _FALSE]);
			unset($argument);
		}
		catch (Exception $e)
		{
			$check = true;
		}
		$this->assume(__METHOD__,	$check,	true);
	}

	//-------------------------------------------------------------------------------- testLogicalNot
	public function testLogicalNot()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::notOp(_TRUE)]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE NOT (t0.`number` = "true")'
		);
	}

	//----------------------------------------------------------------------------- testLogicalNotAnd
	public function testLogicalNotAnd()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::notOp(Func::andOp([_TRUE, _FALSE]))]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE NOT ((t0.`number` = "true" AND t0.`number` = "false"))'
		);
	}

	//------------------------------------------------------------------------- testLogicalNotNegated
	public function testLogicalNotNegated()
	{
		$argument = Func::notOp(_TRUE);
		$argument->negate();
		$builder = new Select(
			Order::class,
			null,
			['number' => $argument]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE (t0.`number` = "true")'
		);
	}

	//----------------------------------------------------------------------------- testLogicalNotNot
	public function testLogicalNotNot()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::notOp(Func::notOp(_TRUE))]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE NOT (NOT (t0.`number` = "true"))'
		);
	}

	//--------------------------------------------------------------------------------- testLogicalOr
	public function testLogicalOr()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::orOp([_TRUE, _FALSE])]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE (t0.`number` = "true" OR t0.`number` = "false")'
		);
	}

	//---------------------------------------------------------------------------- testLogicalOrInAnd
	public function testLogicalOrInAnd()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::orOp([
				Func::orOp([_TRUE, _FALSE]),
				Func::orOp([_TRUE, _FALSE]),
			])]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE ((t0.`number` = "true" OR t0.`number` = "false")'
			. ' OR (t0.`number` = "true" OR t0.`number` = "false"))'
		);
	}

	//------------------------------------------------------ testLogicalTrueShouldNotBeCalledDirectly
	public function testLogicalTrueShouldNotBeCalledDirectly()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => new Logical(Logical::TRUE_OPERATOR, _TRUE)]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE (t0.`number` = "true")'
		);
	}

	//-------------------------------------------------------------------------------- testLogicalXor
	public function testLogicalXor()
	{
		$builder = new Select(
			Order::class,
			null,
			['number' => Func::xorOp([_TRUE, _FALSE])]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE (t0.`number` = "true" XOR t0.`number` = "false")'
		);
	}

	//------------------------------------------------------------------------- testLogicalXorNegated
	public function testLogicalXorNegated()
	{
		$argument = Func::xorOp([_TRUE, _FALSE]);
		$argument->negate();
		$builder = new Select(
			Order::class,
			null,
			['number' => $argument]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.*' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE NOT ((t0.`number` = "true" XOR t0.`number` = "false"))'
		);
	}

}
