<?php
namespace ITRocks\Framework\Mapper;

/**
 * Internal for Object_Builder_Array.
 * Used during the build process, saves the build state.
 */
class Object_Builder_Array_Tool
{

	//---------------------------------------------------------------------------------------- $array
	/**
	 * The source array
	 *
	 * @var array
	 */
	public $array;

	//------------------------------------------------------------------------------------- $fast_add
	/**
	 * @var boolean
	 */
	public $fast_add = false;

	//------------------------------------------------------------------------- $ignore_property_name
	/**
	 * The name of a property to ignore
	 *
	 * @var string
	 */
	public $ignore_property_name;

	//-------------------------------------------------------------------------------------- $is_null
	/**
	 * true if the built object is still null
	 *
	 * @var boolean
	 */
	public $is_null;

	//-------------------------------------------------------------------------------- $null_if_empty
	/**
	 * true if we want to return null instead of an empty object if all properties value is empty or
	 * default.
	 *
	 * @var boolean
	 */
	public $null_if_empty;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The built object
	 *
	 * @var object
	 */
	public $object;

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * @var array
	 */
	public $objects = [];

	//------------------------------------------------------------------------------ $read_properties
	/**
	 * @var array
	 */
	public $read_properties = [];

	//--------------------------------------------------------------------------------------- $search
	/**
	 * @var array|null
	 */
	public $search;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $array                array
	 * @param $object               object
	 * @param $null_if_empty        boolean
	 * @param $ignore_property_name string
	 * @param $search               string[]|null
	 */
	public function __construct(
		array $array, $object, $null_if_empty, $ignore_property_name, array $search = null
	) {
		$this->array                = $array; // arrayToTree($array); // no ! builder do this !
		$this->ignore_property_name = $ignore_property_name;
		$this->is_null              = $null_if_empty;
		$this->null_if_empty        = $null_if_empty;
		$this->object               = $object;
		$this->search               = $search;
	}

}
