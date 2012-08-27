<?php

class Default_Controller implements Controller
{

	/**
	 * @var string
	 */
	private $feature;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($feature)
	{
		$this->feature = $feature;
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param array $params
	 * @param array $form
	 * @param array $files
	 */
	public function call($params, $form, $files)
	{
		
	}

}
