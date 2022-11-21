<?php
namespace ITRocks\Framework\Dao\Mysql\Information_Schema;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
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
	public string $class_name;

	//------------------------------------------------------------------------------------ $composite
	/**
	 * The composite object of the first of $objects, if there are elements, and they are components
	 * Set by composite()
	 *
	 * @var ?object
	 */
	protected ?object $composite = null;

	//--------------------------------------------------------------------------- $composite_property
	/**
	 * The composite property, if there are elements, and they are components
	 * Set by composite()
	 *
	 * @var Reflection_Property
	 */
	protected Reflection_Property $composite_property;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The reference locked / locker object
	 *
	 * @var object
	 */
	public object $object;

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * The lock (locking / locked) objects themselves
	 *
	 * @var object[] Lock objects
	 */
	public array $objects;

	//-------------------------------------------------------------------------------- $objects_count
	/**
	 * Real objects count, as Lock_Objects may be called with only a few objects (2) on big results
	 *
	 * @var integer
	 */
	public int $objects_count;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * Locking property name into lock objects
	 *
	 * @var string
	 */
	public string $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $object        object
	 * @param $class_name    string
	 * @param $property_name string
	 * @param $objects       object[]
	 * @param $objects_count integer|null
	 */
	public function __construct(
		object $object, string $class_name, string $property_name, array $objects,
		int $objects_count = null
	) {
		$this->object        = $object;
		$this->class_name    = Builder::className($class_name);
		$this->property_name = $property_name;
		$this->objects       = $objects;
		$this->objects_count = ($objects_count ?? count($objects));
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->display();
	}

	//------------------------------------------------------------------------------------- composite
	/**
	 * @return ?object
	 */
	public function composite() : ?object
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
	public function display() : string
	{
		$object = $this->composite() ? $this->composite : reset($this->objects);
		if ($this->objects_count > 1) {
			$displays = Loc::tr(Names::classToDisplays(get_class($object)));
			return $this->objects_count . SP . $displays;
		}
		elseif ($this->objects) {
			$display = Loc::tr(Names::classToDisplay(get_class($object)));
			return $display . SP . $object;
		}
		return '';
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * @return string
	 */
	public function link() : string
	{
		if (!$this->objects) {
			return '';
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
		if ($this->objects_count > 1) {
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
