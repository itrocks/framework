<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;

/**
 * For any class that has counter
 * Conception to extends Document, for testing use
 *
 * @extends Document
 * @see Document
 */
trait Has_Counter
{

	//------------------------------------------------------------------------------------ setCounter
	/**
	 * @param $counter integer
	 */
	abstract public function setCounter($counter);

	//------------------------------------------------------------------------------------- setNumber
	/**
	 * @before ITRocks\Framework\Dao\Data_Link::write($this)
	 */
	public function setNumber()
	{
		if (isA($this, Has_Counter::class)) {
			$counter = Dao::searchOne(['class_name' => get_class($this)], Counter::class);
			if (!isset($counter)) {
				/** @noinspection PhpUnhandledExceptionInspection constant */
				$counter = Builder::create(Counter::class, [get_class($this)]);
			}
			$this->setCounter($counter->increment());
		}
	}

}
