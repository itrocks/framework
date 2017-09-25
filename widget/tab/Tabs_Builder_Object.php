<?php
namespace ITRocks\Framework\Widget\Tab;

use Exception;
use ITRocks\Framework\Reflection\Annotation\Class_\Group_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\Widget\Tab;

/**
 * Tabs builder : build tabs for an object
 */
class Tabs_Builder_Object extends Tabs_Builder_Class
{

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * Build tabs containing object properties
	 *
	 * This fills in properties 'display' and 'value' special properties, usefull ie for
	 * Html_Template_Functions
	 *
	 * @param $object            object
	 * @param $filter_properties string[]
	 * @return Tab[] tabs will contain Reflection_Property_Value[] as content
	 */
	public function buildObject($object, array $filter_properties)
	{
		$this->class       = new Reflection_Class(get_class($object));
		$group_annotations = Group_Annotation::allOf($this->class);
		$this->removeDuplicateProperties($group_annotations);
		$this->mergeGroups($group_annotations);
		$this->sortGroups($group_annotations);
		$properties = $this->groupsToProperties($object, $group_annotations, $filter_properties);
		if ($filter_properties) {
			$properties_set = new Set(Reflection_Property_Value::class, $properties);
			$properties     = $properties_set->filterAndSort($filter_properties);
		}
		return parent::buildProperties($properties, $group_annotations);
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @param $object        object
	 * @param $property_path string
	 * @return Reflection_Property_Value
	 * @throws Exception
	 */
	protected function getProperty($object, $property_path)
	{
		if (!is_object($object)) {
			throw new Exception('$object parameter must be an object');
		}
		return new Reflection_Property_Value(get_class($object), $property_path, $object, false, true);
	}

}
