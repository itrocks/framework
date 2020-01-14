<?php
namespace ITRocks\Framework\Dao\Mysql\Information_Schema;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 * Lock objects
 */
class Lock_Objects
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * The class name of all $objects
	 *
	 * @var string Class name
	 */
	public $class_name;

	//------------------------------------------------------------------------------------ $composite
	/**
	 * The composite object of the first of $objects, if there are elements and they are components
	 * Set by composite()
	 *
	 * @var object
	 */
	protected $composite;

	//--------------------------------------------------------------------------- $composite_property
	/**
	 * The composite property, if there are elements and they are components
	 * Set by composite()
	 *
	 * @var Reflection_Property
	 */
	protected $composite_property;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The reference locked / locker object
	 *
	 * @var object
	 */
	public $object;

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * The lock (locking / locked) objects themselves
	 *
	 * @var object[] Lock objects
	 */
	public $objects;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * Locking property name into lock objects
	 *
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object
	 * @param $class_name    string
	 * @param $property_name string
	 * @param $objects       object[]
	 */
	public function __construct($object, $class_name, $property_name, $objects)
	{
		$this->object        = $object;
		$this->class_name    = $class_name;
		$this->property_name = $property_name;
		$this->objects       = $objects;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->display();
	}

	//------------------------------------------------------------------------------------- composite
	/**
	 * @return object
	 */
	public function composite()
	{
		$object     = reset($this->objects);
		$class_name = $this->class_name;
		if ($object && !isset($this->composite) && isA($class_name, Component::class)) {
			/** @var $class_name Component */
			$composite_property = $class_name::getCompositeProperty();
			if ($composite_property) {
				$this->composite          = $object->getComposite(null, $composite_property->name);
				$this->composite_property = $composite_property;
			}
		}
		return $this->composite;
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		$object = $this->composite() ? $this->composite : reset($this->objects);
		if (count($this->objects) > 1) {
			$displays = Loc::tr(Names::classToDisplay(get_class($object)));
			return count($this->objects) . SP . $displays;
		}
		elseif ($this->objects) {
			$display = Loc::tr(Names::classToDisplay(get_class($object)));
			return $display . SP . strval($object);
		}
		return null;
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * @return string
	 */
	public function link()
	{
		if (!$this->objects) {
			return null;
		}
		$object               = reset($this->objects);
		$class_name           = get_class($object);
		$search_property_name = $this->property_name;
		if ($this->composite()) {
			$object               = $this->composite;
			$class_name           = get_class($object);
			$search_property_name = Foreign_Annotation::of($this->composite_property)->value
				. DOT . $search_property_name;
		}
		if (count($this->objects) > 1) {
			return View::link(
				$class_name,
				Feature::F_LIST,
				null,
				[
					'add_property'                          => $search_property_name,
					'search[' . $search_property_name . ']' => strval($this->object)
				]
			);
		}
		return View::link($object, Feature::F_OUTPUT);
	}

}
