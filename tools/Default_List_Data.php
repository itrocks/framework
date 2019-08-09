<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * A default list data set class : this stores data (visible strings and linked object) for list views
 */
class Default_List_Data extends Set implements List_Data
{

	//------------------------------------------------------------------------------------ $functions
	/**
	 * @var Dao_Function[]
	 */
	private $functions;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Reflection_Property[] The key is the the path of the property
	 */
	private $properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $element_class_name string the reference class name
	 * @param $properties         Reflection_Property[] the key must be the path of the property
	 * @param $functions          Dao_Function[]
	 */
	public function __construct($element_class_name, array $properties, array $functions = [])
	{
		parent::__construct($element_class_name);
		$this->functions  = $functions;
		$this->properties = $properties;
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $row     List_Row a row element
	 * @param $element null The element should always be null, we only need the row
	 */
	public function add($row, $element = null)
	{
		parent::add($row, null);
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return integer
	 */
	public function count()
	{
		return count($this->properties);
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return Reflection_Class
	 */
	public function getClass()
	{
		return $this->elementClass();
	}

	//---------------------------------------------------------------------------------- getFunctions
	/**
	 * @return Dao_Function[]
	 */
	public function getFunctions()
	{
		return $this->functions;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @param $row_index integer
	 * @return object
	 */
	public function getObject($row_index)
	{
		return $this->getRow($row_index)->getObject();
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets properties names list
	 *
	 * @return Reflection_Property[]
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	//---------------------------------------------------------------------------------------- getRow
	/**
	 * @param $row_index integer
	 * @return List_Row
	 */
	public function getRow($row_index)
	{
		/** @var $list_row List_Row */
		$list_row = $this->get($row_index);
		return $list_row;
	}

	//--------------------------------------------------------------------------------------- getRows
	/**
	 * @return List_Row[]
	 */
	public function getRows()
	{
		return $this->elements;
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * @param $row_index integer
	 * @param $property  string
	 * @return mixed
	 */
	public function getValue($row_index, $property)
	{
		return $this->getRow($row_index)->getValue($property);
	}

	//---------------------------------------------------------------------------------------- newRow
	/**
	 * Creates a new row
	 *
	 * @param $class_name string The class name of the main business object stored into the row
	 * @param $object     object The main business object stored into the row
	 * @param $row        array|object The data stored into the row
	 * @return List_Row
	 */
	public function newRow($class_name, $object, $row)
	{
		return new Default_List_Row($class_name, $object, $row, $this);
	}

}
