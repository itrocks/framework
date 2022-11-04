<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * Takes a collection of objects and build an HTML output containing their data
 *
 * This is specifically for multiple objects of different classes extending the same abstract parent
 * class.
 */
class Abstract_Collection
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public string $class_name;

	//----------------------------------------------------------------------------------- $collection
	/**
	 * @var object[]
	 */
	public array $collection;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public Reflection_Property $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property   Reflection_Property
	 * @param $collection object[]
	 */
	public function __construct(Reflection_Property $property, array $collection)
	{
		$this->class_name = $property->getType()->getElementTypeAsString();
		$this->collection = $collection;
		$this->property   = $property;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build() : string
	{
		$result = '';
		foreach ($this->collection as $object) {
			$parameters = [$object, Parameter::IS_INCLUDED => true, Template::TEMPLATE => 'object'];
			$result .= View::run($parameters, [], [], get_class($object), 'output');
		}
		return $result;
	}

}
