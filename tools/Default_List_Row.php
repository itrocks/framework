<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_View;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Has_Object_Class;

/**
 * The list row class for Default_List_Data
 */
class Default_List_Row implements List_Row
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public string $class_name;

	//----------------------------------------------------------------------------------------- $list
	/**
	 * @var List_Data
	 */
	public List_Data $list;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var mixed Object or object identifier
	 */
	public mixed $object;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var array
	 */
	public array $values;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $object     object|mixed
	 * @param $values     string[]
	 * @param $list       List_Data
	 */
	public function __construct(string $class_name, mixed $object, array $values, List_Data $list)
	{
		$this->class_name = $class_name;
		$this->list       = $list;
		$this->object     = $object;
		$this->values     = $values;
	}

	//----------------------------------------------------------------------------------------- count
	/**
	 * @return integer
	 */
	public function count() : int
	{
		return count($this->values);
	}

	//---------------------------------------------------------------------------------- formatValues
	/**
	 * Return values ready for display
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string[]
	 * @see formatValuesEx
	 */
	public function formatValues() : array
	{
		$values = [];
		static $cache = [];
		foreach ($this->values as $property_path => $value) {
			/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
			$property_view = $cache[$this->class_name][$property_path] ?? (
				$cache[$this->class_name][$property_path] = new Reflection_Property_View(
					new Reflection_Property($this->class_name, $property_path)
				)
				);
			$values[$property_path] = $property_view->formatValue($value);
		}
		return $values;
	}

	//-------------------------------------------------------------------------------- formatValuesEx
	/**
	 * Return values ready for display as an array with property_path and value for each row
	 * This is more suitable than formatValues() if you want your template to deal with property_path
	 *
	 * @return string[]
	 */
	public function formatValuesEx() : array
	{
		$properties = $this->list->getProperties();
		$translate  = Loc::formatTranslate(false);
		$values     = $this->formatValues();
		Loc::formatTranslate($translate);
		foreach ($values as $property_path => $value) {
			$values[$property_path] = [
				'path'     => $property_path,
				'property' => $properties[$property_path],
				'value'    => $value,
			];
		}
		return $values;
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * @return string
	 */
	public function getClassName() : string
	{
		return $this->class_name;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * @return object
	 */
	public function getObject() : object
	{
		Getter::getObject($this->object, $this->class_name);
		return $this->object;
	}

	//--------------------------------------------------------------------------- getObjectIdentifier
	/**
	 * @return mixed
	 */
	public function getObjectIdentifier() : mixed
	{
		return is_object($this->object) ? Dao::getObjectIdentifier($this->object) : $this->object;
	}

	//--------------------------------------------------------------------------------- getOutputLink
	/**
	 * Returns link to the output feature for the object
	 * May be null if link is deactivated, eg by an ACL feature
	 *
	 * @noinspection PhpUnused html
	 * @return string|null
	 */
	public function getOutputLink() : ?string
	{
		return View::link(
			is_object($this->object) ? $this->object : [$this->class_name, $this->object]
		);
	}

	//------------------------------------------------------------------------- getOutputLinkProtocol
	/**
	 * @noinspection PhpUnused list_/body.html
	 * @return string
	 */
	public function getOutputLinkProtocol() : string
	{
		return 'app://';
	}

	//--------------------------------------------------------------------------- getOutputLinkTarget
	/**
	 * @noinspection PhpUnused list_/body.html
	 * @return string
	 */
	public function getOutputLinkTarget() : string
	{
		return Target::MAIN;
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * @param $property string
	 * @return mixed
	 */
	public function getValue(string $property) : mixed
	{
		return $this->values[$property];
	}

	//------------------------------------------------------------------------------------- getValues
	/**
	 * @return array
	 */
	public function getValues() : array
	{
		return $this->values;
	}

	//-------------------------------------------------------------------------------------------- id
	/**
	 * @return mixed
	 */
	public function id() : mixed
	{
		return is_object($this->object) ? Dao::getObjectIdentifier($this->object) : $this->object;
	}

	//----------------------------------------------------------------------------------- objectClass
	/**
	 * @return ?string
	 */
	public function objectClass() : ?string
	{
		if (is_a($this->class_name, Has_Object_Class::class, true)) {
			/** @var $object Has_Object_Class */
			$object = $this->getObject();
			return $object->objectClass();
		}
		return null;
	}

	//-------------------------------------------------------------------------------------- setValue
	/**
	 * @param $property string the path of the property
	 * @param $value    mixed the new value
	 */
	public function setValue(string $property, mixed $value)
	{
		$this->values[$property] = $value;
	}

}
