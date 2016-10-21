<?php
namespace SAF\Framework\Dao\Option;

use SAF\Framework\Dao;
use SAF\Framework\Tests\Test;

/**
 * Dao options tests
 */
class Tests extends Test
{

	//-------------------------------------------------------------------------------------- testOnly
	public function testOnly()
	{
		$this->method(__METHOD__);

		$assume = Dao::only([]);
		$assume->properties = ['one', 'two', 'three', 'four'];


		$this->assume('arguments', Dao::only('one', 'two', 'three', 'four'), $assume);
		$this->assume('array',     Dao::only(['one', 'two', 'three', 'four']), $assume);
		$this->assume('mixed',     Dao::only('one', ['two', 'three'], 'four'), $assume);
		$this->assume('mixed2',    Dao::only(['one', 'two'], 'three', ['four']), $assume);
	}

}
