<?php
namespace ITRocks\Framework\View\Html\Template;

/**
 * A properties block
 */
class Block
{

	//----------------------------------------------------------------------------------------- $data
	/**
	 * @var string[] key is the HTML attribute name ('data-name'), value is the associated value
	 */
	protected $data;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_name string
	 * @param $data          string[]
	 */
	public function __construct($property_name, $data = [])
	{
		$this->data          = $data;
		$this->property_name = $property_name;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->property_name;
	}

	//------------------------------------------------------------------------------------------ data
	/**
	 * Gets data using HTML form
	 *
	 * @example 'data-conditions="other_property=value" data-combo-key="118"'
	 * @return string
	 */
	public function data()
	{
		$result = [];
		foreach ($this->data as $key => $value) {
			$result[] = 'data-' . $key . '=' . DQ . $value . DQ;
		}
		return join(SP, $result);
	}

}
