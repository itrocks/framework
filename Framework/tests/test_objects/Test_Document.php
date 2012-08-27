<?php

class Test_Document
{

	/**
	 * @mandatory
	 * @var string
	 */
	private $date;

	/**
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
