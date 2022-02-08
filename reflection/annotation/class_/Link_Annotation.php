<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Annoted;
use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Link_Class;

/**
 * This tells that the class is a link class
 *
 * It means that :
 * - its data storage set naming will be appended by a '_links'
 * - there will be no data storage field creation for parent linked table into this data storage set
 *   but a link field
 *
 * @example '@link User' means that the inherited class of User is linked to the parent class User
 * - data storage fields will be those from this class, and immediate parent classes if they are not 'User'
 * - an additional implicit data storage field will link to the class 'User'
 */
class Link_Annotation extends Annotation implements Class_Context_Annotation
{
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'link';

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class|Annoted|null
	 */
	public Reflection_Class|null $class;

	//------------------------------------------------------------------------------ $link_properties
	/**
	 * Finally will be string[], once you called getLinkProperties()
	 *
	 * Before : contains data to help to get them :
	 * - if a string : contains the properties names, separated by spaces
	 * - if a Reflection_Class : this will be the class to be scanned for @composite properties
	 *
	 * This is for optimization purpose : no calculation will be done if you don't need this data
	 *
	 * @var string[]|string|null
	 */
	private array|string|null $link_properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Annotation string value is a class name followed by the two property names that do the link
	 *
	 * @example '@var Class_Name property_1 property_2'
	 * @param $value ?string
	 * @param $class Reflection_Class The contextual Reflection_Class object
	 */
	public function __construct(?string $value, Reflection_Class $class)
	{
		$value = strval($value);
		if ($value && !str_starts_with($value, 'http')) {
			$this->class           = $class;
			$this->link_properties = [];
			if (trim($value)) {
				$i = strpos($value, SP);
				if ($i === false) {
					parent::__construct($value);
					$this->link_properties = null;
				}
				else {
					parent::__construct(substr($value, 0, $i));
					$this->link_properties = substr($value, $i + 1);
				}
			}
			else {
				parent::__construct('');
			}
		}
		else {
			parent::__construct('');
		}
	}

	//---------------------------------------------------------------------------------- getLinkClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Link_Class
	 */
	public function getLinkClass() : Link_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection valid $this->class->getName() */
		return new Link_Class($this->class->getName());
	}

	//----------------------------------------------------------------------------- getLinkProperties
	/**
	 * Get link properties names list
	 *
	 * @return Reflection_Property[] The key contains the name of the property
	 */
	public function getLinkProperties() : array
	{
		if (!isset($this->link_properties) || !is_array($this->link_properties)) {
			$text_link_properties  = $this->link_properties ?? null;
			$this->link_properties = [];
			if ($text_link_properties) {
				$this->setLinkPropertiesByNames(
					is_string($text_link_properties)
						? explode(SP, $text_link_properties)
						: $text_link_properties
				);
			}
			elseif (isset($this->class)) {
				$this->setLinkPropertiesByClass($this->class);
			}
		}
		return $this->link_properties;
	}

	//---------------------------------------------------------------------- setLinkPropertiesByClass
	/**
	 * Scan @link class for @composite properties, which are the link properties
	 *
	 * Do not get @composite properties from parent classes : only the higher class containing
	 * composite properties match them to link properties.
	 *
	 * @param $class Reflection_Class
	 */
	protected function setLinkPropertiesByClass(Reflection_Class $class)
	{
		if ($this->link_properties) {
			return;
		}
		while ($class) {
			// if properties names are not set : get explicit composite properties names
			foreach ($class->getProperties([T_USE]) as $property) {
				if (
					$property->getAnnotation('composite')->value
					|| $property->getAnnotation('link_composite')->value
				) {
					$this->link_properties[$property->getName()] = $property;
				}
			}
			$class = $class->getParentClass();
			if ($class->getName() === $this->value) {
				$class = null;
			}
		}
	}

	//---------------------------------------------------------------------- setLinkPropertiesByNames
	/**
	 * If properties names are told, this will be faster to get their names here
	 *
	 * @param $link_properties string[]
	 */
	protected function setLinkPropertiesByNames(array $link_properties)
	{
		$properties = $this->class->getProperties([T_EXTENDS, T_USE]);
		foreach ($link_properties as $property_name) {
			if ($property_name) {
				$this->class = null;
				$this->link_properties[$property_name] = $properties[$property_name];
			}
		}
	}

}
