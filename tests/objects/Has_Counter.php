<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * For any class that has counter
 * Conception to extend Document, for testing use
 */
#[Extend(Document::class)]
trait Has_Counter
{

	//------------------------------------------------------------------------------------ setCounter
	abstract public function setCounter(int $counter) : void;

	//------------------------------------------------------------------------------------- setNumber
	/**
	 * @before ITRocks\Framework\Dao\Data_Link::write($this)
	 */
	public function setNumber() : void
	{
		if (!isA($this, Has_Counter::class)) {
			return;
		}
		$counter = Dao::searchOne(['class_name' => get_class($this)], Counter::class);
		if (!isset($counter)) {
			/** @noinspection PhpUnhandledExceptionInspection constant */
			$counter = Builder::create(Counter::class, [get_class($this)]);
		}
		$this->setCounter($counter->increment());
	}

}
