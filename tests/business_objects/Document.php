<?php
namespace SAF\Tests;

abstract class Document
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * Document date
	 *
	 * @mandatory
	 * @var \SAF\Framework\Date_Time
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
	public function __construct($date = null, $number = null)
	{
		$this->date = $date;
		$this->number = $number;
	}

}
