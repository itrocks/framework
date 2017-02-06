<?php
namespace ITRocks\Framework\Webservice\Json;

/**
 * An autocomplete entry class
 *
 * This object is recognized by jQuery-ui autocomplete component to be an entry for <option>
 *
 * TODO Json controller is not only for Autocomplete : name must be more global
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
