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

	//--------------------------------------------------------------------------------- $has_workflow
	/**
	 * Document should be sent through workflow
	 *
	 * @mandatory
	 * @var boolean
	 */
	private $has_workflow;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $date         string|Date_Time
	 * @param $number       string
	 * @param $has_workflow boolean
	 */
	public function __construct($date = null, $number = null, $has_workflow = false)
	{
		$this->date = $date;
		$this->number = $number;
		$this->has_workflow = $has_workflow;
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
