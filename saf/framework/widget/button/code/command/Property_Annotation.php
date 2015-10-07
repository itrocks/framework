<?php
namespace SAF\Framework\Widget\Button\Code\Command;

use SAF\Framework\Builder;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Reflection\Annotation\Parser;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tools\Names;
use SAF\Framework\Widget\Button\Code\Command;
use SAF\Framework\Widget\Validate\Property\Mandatory_Annotation;

/**
 * Change a property annotation value during the execution of the current script
 *
 * @example translated property name : mandatory
 */
class Property_Annotation implements Command
{

	//------------------------------------------------------------------------------------- $annotate
	/**
	 * @var string
	 */
	public $annotate;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_name string
	 * @param $annotate      string
	 */
	public function __construct($property_name, $annotate)
	{
		$this->property_name = Names::displayToProperty(Loc::rtr($property_name));
		$this->annotate      = Loc::rtr($annotate);
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $object object
	 * @return boolean
	 */
	public function execute($object)
	{
		$property = new Reflection_Property(get_class($object), $this->property_name);
		$annotate = $this->annotate;
		$annotation_class = Parser::getAnnotationClassName(Reflection_Property::class, $annotate);
		if ($annotation_class) {
			/** @var $annotation Mandatory_Annotation */
			$annotation = Builder::create($annotation_class, [true, $property]);
			$property->setAnnotation('mandatory', $annotation);
		}
		else {
			return false;
		}
		return true;
	}

}
