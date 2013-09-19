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
	 * @param $data       array
	 * @param $properties string[]
	 */
	public function __construct($data = null, $properties = null)
	{
		if (isset($properties)) {
			$this->properties = $properties;
		}
		if (isset($data)) {
			$this->data = $data;
			if (!isset($this->properties)) {
				reset($data);
				$this->properties = next($data);
			}
		}
	}

}
