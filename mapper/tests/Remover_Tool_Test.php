<?php
namespace ITRocks\Framework\Mapper\Tests;

use ITRocks\Framework\Mapper\Remover_Tool;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Objects\Order_Line;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Remover_Tools unit tests
 */
class Remover_Tool_Test extends Test
{

	//----------------------------------------------------------------- testRemoveObjectFromComposite
	/**
	 * Test Remover_Tool::removeObjectFromComposite
	 */
	public function testRemoveObjectFromComposite()
	{
		$order = new Order(new Date_Time('2017-01-26 12:34:00'), '0001');
		$line1 = new Order_Line(1);
		$line2 = new Order_Line(2);
		$line3 = new Order_Line(3);
		$order->addLines([$line1, $line2, $line3]);
		Remover_Tool::removeObjectFromComposite($order, $line2);

		$assume = new Order(new Date_Time('2017-01-26 12:34:00'), '0001');
		$assume->setLines([0 => $line1, 2 => $line3]);

		static::assertEquals($assume, $order);
	}

}
