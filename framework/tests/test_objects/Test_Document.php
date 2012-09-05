<?php
namespace SAF\Framework\Tests;

class Test_Document
{

	/**
	 * @mandatory
	 * @var string
	 */
	public $date;

	/**
	 * @mandatory
	 * @var string
	 */
	public $number;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($date = null, $number = null)
	{
		$this->date = $date;
		$this->number = $number;
	}

}
