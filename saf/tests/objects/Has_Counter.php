<?php
namespace SAF\Tests\Objects;

use SAF\Framework\Builder;
use SAF\Framework\Dao;

/**
 * For any class that has counter
 */
trait Has_Counter
{

	/**
	 * @param $counter integer
	 */
	abstract public function setCounter($counter);

	//------------------------------------------------------------------------------------- setNumber
	/**
	 * @before \SAF\Framework\Data_Link::write($this)
	 */
	public function setNumber()
	{
		if (isA($this, Has_Counter::class)) {
			/** @var $counter Counter*/
			$counter = Dao::searchOne(['class_name' => get_class($this)], Counter::class);
			if (!isset($counter)) {
				$counter = Builder::create(Counter::class, get_class($this));
			}
			$this->setCounter($counter->increment());
		}
	}

}
