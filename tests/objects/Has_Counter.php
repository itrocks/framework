<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;

/**
 * For any class that has counter
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
			/** @var $counter Counter */
			$counter = Dao::searchOne(['class_name' => get_class($this)], Counter::class);
			if (!isset($counter)) {
				$counter = Builder::create(Counter::class, [get_class($this)]);
			}
			$this->setCounter($counter->increment());
		}
	}

}
