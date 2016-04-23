<?php
namespace SAF\Framework\Tests\Objects;

use SAF\Framework\Tools\Date_Time;

/**
 * A document class
 */
abstract class Document
{
	use Has_Counter;

	//----------------------------------------------------------------------------------------- $date
	/**
	 * Document date
	 *
	 * @mandatory
	 * @var Date_Time
	 */
	private $date;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Document number
	 *
	 * @mandatory
	 * @var string
	 */
	private $number;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $date   string|Date_Time
	 * @param $number string
	 */
	public function __construct($date = null, $number = null)
	{
		$this->date = $date;
		$this->number = $number;
	}

	//------------------------------------------------------------------------------------ setCounter
	/**
	 * @param $counter integer
	 */
	public function setCounter($counter)
	{
		$this->number = $counter;
	}

}
