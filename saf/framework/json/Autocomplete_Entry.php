<?php
namespace SAF\Framework\Json;

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $id    integer
	 * @param $value string
	 */
	public function __construct($id = null, $value = null)
	{
		if (isset($id))    $this->id = $id;
		if (isset($value)) $this->value = $value;
	}

}
