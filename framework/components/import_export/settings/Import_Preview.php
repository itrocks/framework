<?php
namespace SAF\Framework;

/**
 * Import preview data
 */
class Import_Preview
{

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var array two dimensional array (keys are row, col) with written data as value
	 */
	public $data;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[] property names
	 */
	public $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $properties string[]
	 * @param $data       array
	 */
	public function __construct($properties = null, $data = null)
	{
		if (isset($data))       $this->data       = $data;
		if (isset($properties)) $this->properties = $properties;
	}

}
