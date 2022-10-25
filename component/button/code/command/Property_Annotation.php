<?php
namespace ITRocks\Framework\Component\Button\Code\Command;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Button\Code\Command;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Mandatory_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Names;

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
	public string $annotate;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public string $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_name string
	 * @param $annotate      string
	 */
	public function __construct(string $property_name, string $annotate)
	{
		$this->property_name = Names::displayToProperty(Loc::rtr($property_name));
		$this->annotate      = Loc::rtr($annotate);
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return boolean
	 */
	public function execute(object $object) : bool
	{
		/** @noinspection PhpUnhandledExceptionInspection property must belong to object */
		$property         = new Reflection_Property($object, $this->property_name);
		$annotate         = $this->annotate;
		$annotation_class = Annotation\Parser::getAnnotationClassName(
			Reflection_Property::class, $annotate
		);
		if (!$annotation_class) {
			return false;
		}
		/** @noinspection PhpUnhandledExceptionInspection valid annotation class name */
		/** @var $annotation Mandatory_Annotation */
		$annotation = Builder::create($annotation_class, [true, $property]);
		$property->setAnnotation('mandatory', $annotation);
		return true;
	}

}
