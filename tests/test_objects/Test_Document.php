<?php
namespace SAF\Framework\Tests;

abstract class Test_Document
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
