<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Tools\Date_Time;

/**
 * A document class
 *
 * @extends Object
 * @store_name test_documents
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

	//--------------------------------------------------------------------------------- $has_workflow
	/**
	 * Document should be sent through workflow
	 *
	 * @mandatory
	 * @var boolean
	 */
	public $has_workflow;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Document number
	 *
	 * @mandatory
	 * @search_range
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
		if (isset($date))   $this->date   = $date;
		if (isset($number)) $this->number = $number;
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
