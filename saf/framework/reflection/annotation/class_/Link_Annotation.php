<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Builder;
use SAF\Framework\PHP;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;
use SAF\Framework\Reflection\Interfaces\Reflection_Class;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;
use SAF\Framework\Reflection\Link_Class;

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

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public $class;

	//------------------------------------------------------------------------------ $link_properties
	/**
	 * Finally will be string[], once you called getLinkProperties()
	 *
	 * Before : contains data to help getting them :
	 * - if a string : contains the properties names, separated by spaces
	 * - if a Reflection_Class : this will be the class to be scanned for @composite properties
	 *
	 * This is for optimization purpose : no calculation will be done if you don't need this data
	 *
	 * @var string[]|string
	 */
	private $link_properties;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Annotation string value is a class name followed by the two property names that do the link
	 *
	 * @example '@var Class_Name property_1 property_2'
	 * @param $value string
	 * @param $class Reflection_Class The contextual Reflection_Class object
	 */
	public function __construct($value, Reflection_Class $class)
	{
		if ($value && (substr($value, 0, 4) !== 'http')) {
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
				parent::__construct(null);
			}
		}
		else {
			parent::__construct(null);
		}
	}

	//---------------------------------------------------------------------------------- getLinkClass
	/**
	 * @return Link_Class
	 */
	public function getLinkClass()
	{
		return new Link_Class($this->class->getName());
	}

	//----------------------------------------------------------------------------- getLinkProperties
	/**
	 * Get link properties names list
	 *
	 * @return Reflection_Property[] The key contains the name of the property
	 */
	public function getLinkProperties()
	{
		if (!is_array($this->link_properties)) {
			$temp = $this->link_properties;
			$this->link_properties = [];
			if (is_string($temp)) {
				// if properties names are told, this will be faster to get their names here
				$properties = $this->class->getProperties([T_EXTENDS, T_USE]);
				foreach (explode(SP, $temp) as $property_name) {
					if ($property_name) {
						$this->link_properties[$property_name] = $properties[$property_name];
					}
				}
			}
			elseif ($this->class) {
				// if properties names are not set : get explicit composite properties names
				foreach ($this->class->getProperties([T_EXTENDS, T_USE]) as $property) {
					if ($property->getAnnotation('composite')->value) {
						$this->link_properties[$property->getName()] = $property;
					}
				}
			}
		}
		return $this->link_properties;
	}

}
