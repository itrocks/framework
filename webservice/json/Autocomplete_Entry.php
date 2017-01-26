<?php
namespace ITRocks\Framework\Webservice\Json;

/**
 * An autocomplete entry class
 *
 * This object is recognized by jQuery-ui autocomplete component to be an entry for <option>
 */
class Autocomplete_Entry
{

	//------------------------------------------------------------------------------------------- $id
	/**
	 * @var integer
	 */
	public $id;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public $value;

	//------------------------------------------------------------------------------ $data_attributes
	/**
	 * @var string[]
	 */
	public $data_attributes;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $id    integer
	 * @param $value string
	 * @param $data_attributes string[] use in the input field
	 *    example : $data_attribute["key"] = "value" render html data-key="value"
	 */
	public function __construct($id = null, $value = null, $data_attributes = null)
	{
		if (isset($id))    $this->id = $id;
		if (isset($value)) $this->value = $value;
		if (isset($data_attributes)) $this->data_attributes = $data_attributes;
	}

}
