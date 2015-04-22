<?php
namespace SAF\Framework\Dao\Func;

use SAF\Framework\Dao\Func;
use SAF\Framework\Sql\Builder\Select;
use SAF\Framework\Tests\Objects\Order;
use SAF\Framework\Tests\Test;

/**
 * Dao functions unit tests
 */
class Tests extends Test
{

	//------------------------------------------------------------------------------------ testLeftOf
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

	//------------------------------------------------------------------------------------ testLeftOf
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

}
