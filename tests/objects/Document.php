<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A document class
 */
#[Store('test_documents')]
abstract class Document
{
	use Has_Counter;

	//----------------------------------------------------------------------------------------- $date
	/** Document date */
	#[Mandatory]
	private Date_Time|string $date;

	//--------------------------------------------------------------------------------- $has_workflow
	/** Document should be sent through workflow */
	#[Mandatory]
	public bool $has_workflow;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Document number
	 *
	 * @search_range
	 */
	#[Mandatory]
	private string $number;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Date_Time|string $date = null, string $number = null)
	{
		if (isset($date))   $this->date   = $date;
		if (isset($number)) $this->number = $number;
	}

	//------------------------------------------------------------------------------------ setCounter
	public function setCounter(int $counter) : void
	{
		$this->number = $counter;
	}

}
