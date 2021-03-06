<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * A default list data set class : this stores data (visible strings and linked object) for list views
 *
 * @override elements @var List_Row[]
 * @property List_Row[] elements
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
	public function __construct(string $element_class_name, array $properties, array $functions = [])
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
	public function count() : int
	{
		return count($this->properties);
	}

	//------------------------------------------------------------------------------------ firstValue
	/**
	 * @return mixed
	 */
	public function firstValue()
	{
		if (!$this->elements) {
			return null;
		}
		$values = reset($this->elements)->getValues();
		return reset($values);
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return Reflection_Class
	 */
	public function getClass() : Reflection_Class
	{
		return $this->elementClass();
	}

	//---------------------------------------------------------------------------------- getFunctions
	/**
	 * @return Dao_Function[]
	 */
	public function getFunctions() : array
	{
		return $this->functions;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @param $row_index integer
	 * @return object
	 */
	public function getObject(int $row_index)
	{
		return $this->getRow($row_index)->getObject();
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets properties names list
	 *
	 * @return Reflection_Property[]
	 */
	public function getProperties() : array
	{
		return $this->properties;
	}

	//---------------------------------------------------------------------------------------- getRow
	/**
	 * @param $row_index integer
	 * @return List_Row
	 */
	public function getRow(int $row_index) : List_Row
	{
		/** @var $list_row List_Row */
		$list_row = $this->get($row_index);
		return $list_row;
	}

	//--------------------------------------------------------------------------------------- getRows
	/**
	 * @return List_Row[]
	 */
	public function getRows() : array
	{
		return $this->elements;
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * @param $row_index integer
	 * @param $property  string
	 * @return mixed
	 */
	public function getValue(int $row_index, string $property)
	{
		return $this->getRow($row_index)->getValue($property);
	}

	//---------------------------------------------------------------------------------------- newRow
	/**
	 * Creates a new row
	 *
	 * @param $class_name string The class name of the main business object stored into the row
	 * @param $object     object The main business object stored into the row
	 * @param $values     array  The values to store into the row
	 * @return List_Row
	 */
	public function newRow(string $class_name, $object, array $values) : List_Row
	{
		return new Default_List_Row($class_name, $object, $values, $this);
	}

}
