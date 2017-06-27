<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Tests\Test;

/**
 * Dao options tests
 */
class Option_Test extends Test
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
