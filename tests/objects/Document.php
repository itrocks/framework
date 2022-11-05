<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Tools\Date_Time;

/**
 * A document class
 *
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
	 * @var Date_Time|string
	 */
	private Date_Time|string $date;

	//--------------------------------------------------------------------------------- $has_workflow
	/**
	 * Document should be sent through workflow
	 *
	 * @mandatory
	 * @var boolean
	 */
	public bool $has_workflow;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Document number
	 *
	 * @mandatory
	 * @search_range
	 * @var string
	 */
	private string $number;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $date   Date_Time|string|null
	 * @param $number string|null
	 */
	public function __construct(Date_Time|string $date = null, string $number = null)
	{
		if (isset($date))   $this->date   = $date;
		if (isset($number)) $this->number = $number;
	}

	//------------------------------------------------------------------------------------ setCounter
	/**
	 * @param $counter integer
	 */
	public function setCounter(int $counter) : void
	{
		$this->number = $counter;
	}

}
