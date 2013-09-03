<?php
namespace SAF\Framework;

/**
 * Html template functions : those which are called using {@functionName} into templates
 */
abstract class Html_Template_Functions
{

	//-------------------------------------------------------------------------------- $inside_blocks
	/**
	 * Used by startingBlocks and stoppingBlocks calls
	 *
	 * @var string[] key equals value
	 */
	private static $inside_blocks = array();

	//-------------------------------------------------------------------------------- getApplication
	/**
	 * Returns application name
	 *
	 * @param $template Html_Template
	 * @return string
	 */
	public static function getApplication(
		/** @noinspection PhpUnusedParameterInspection */ Html_Template $template
	) {
		return new Displayable(
			Configuration::current()->getApplicationName(), Displayable::TYPE_CLASS
		);
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Returns object's class name
	 *
	 * @param $template Html_Template
	 * @return string
	 */
	public static function getClass(Html_Template $template)
	{
		$object = reset($template->objects);
		return is_object($object)
			? (
					($object instanceof Set)
					? new Displayable(Names::classToSet($object->element_class_name), Displayable::TYPE_CLASS)
					: new Displayable(get_class($object), Displayable::TYPE_CLASS)
				)
			: new Displayable($object, Displayable::TYPE_CLASS);
	}

	//-------------------------------------------------------------------------------------- getCount
	/**
	 * Returns array count
	 *
	 * @param $template Html_Template
	 * @return integer
	 */
	public static function getCount(Html_Template $template)
	{
		return count(reset($template->objects));
	}

	//------------------------------------------------------------------------------------ getDisplay
	/**
	 * Return object's display
	 *
	 * @param $template Html_Template
	 * @return string
	 */
	public static function getDisplay(Html_Template $template)
	{
		$object = reset($template->objects);
		if ($object instanceof Reflection_Property) {
			return Names::propertyToDisplay($object->name);
		}
		elseif ($object instanceof Reflection_Class) {
			return Names::classToDisplay($object->name);
		}
		elseif ($object instanceof Reflection_Method) {
			return Names::methodToDisplay($object->name);
		}
		elseif (is_object($object)) {
			return (new Displayable(get_class($object), Displayable::TYPE_CLASS))->display();
		}
		else {
			return $object;
		}
	}

	//--------------------------------------------------------------------------------------- getEdit
	/**
	 * Returns an HTML edit widget for current property or List_Data property
	 *
	 * @param $template Html_Template
	 * @param $prefix   string
	 * @return string
	 */
	public static function getEdit(Html_Template $template, $prefix = null)
	{
		if (count($template->objects) > 2) {
			// from a List_Data
			$property = reset($template->objects);
			next($template->objects);
			$list_data = next($template->objects);
			if ($list_data instanceof Default_List_Data) {
				$class_name = $list_data->element_class_name;
				list($property, $property_path, $value) = self::toEditPropertyExtra($class_name, $property);
				$property_edit = new Html_Builder_Property_Edit($property, $value, $prefix);
				$property_edit->name = $property_path;
				$property_edit->preprop = null;
				return $property_edit->build();
			}
		}
		else {
			// from any sub-part of ...
			$property = self::getObject($template);
			if ($property instanceof Reflection_Property_Value) {
				// ... a Reflection_Property_Value
				return (new Html_Builder_Property_Edit($property, $property->value()))->build();
			}
			elseif ($property instanceof Reflection_Property) {
				// ... a Reflection_Property
				return (new Html_Builder_Property_Edit($property))->build();
			}
			elseif (is_object($property)) {
				// ... an object and it's property name
				$property_name = prev($template->objects);
				$property = Reflection_Property::getInstanceOf($property, $property_name);
				if ($property != null) {
					return (new Html_Builder_Property_Edit($property))->build();
				}
			}
		}
		// default html input widget
		$input = new Html_Input();
		$input->setAttribute("name", reset($template->objects));
		return $input;
	}

	//------------------------------------------------------------------------------------- getExpand
	/**
	 * Returns an expanded list of properties. Source element must be a list of Reflection_Property
	 *
	 * @param $template Html_Template
	 * @return Reflection_Property
	 */
	public static function getExpand(Html_Template $template)
	{
		$property = reset($template->objects);
		$expanded = Integrated_Properties::expandUsingProperty(
			$expanded, $property, $template->getParentObject($property->class)
		);
		return $expanded ? $expanded : array($property);
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * Returns template's feature method name
	 *
	 * @param $template Html_Template
	 * @return Displayable
	 */
	public static function getFeature(Html_Template $template)
	{
		return new Displayable($template->getFeature(), Displayable::TYPE_METHOD);
	}

	//---------------------------------------------------------------------------------------- getHas
	/**
	 * Returns true if the element is not empty
	 * (usefull for conditions on arrays)
	 *
	 * @param $template Html_Template
	 * @return boolean
	 */
	public static function getHas(Html_Template $template)
	{
		$object = reset($template->objects);
		return !empty($object);
	}

	//---------------------------------------------------------------------------------------- getLoc
	/**
	 * Returns a value with application of current locales
	 *
	 * @param $template Html_Template
	 * @return object
	 */
	public static function getLoc(Html_Template $template)
	{
		foreach ($template->objects as $object) {
			if (is_object($object)) {
				$property= Reflection_Property::getInstanceOf($object, reset($template->var_names));
				return Loc::propertyToLocale($property, reset($template->objects));
				break;
			}
		}
		return reset($object);
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Returns nearest object from templating tree
	 *
	 * @param $template Html_Template
	 * @return object
	 */
	public static function getObject(Html_Template $template)
	{
		$object = null;
		foreach ($template->objects as $object) {
			if (is_object($object)) {
				break;
			}
		}
		return $object;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns object's properties, and their display and value
	 *
	 * @param $template Html_Template
	 * @return Reflection_Property_Value[]
	 */
	public static function getProperties(Html_Template $template)
	{
		$object = reset($template->objects);
		$properties_filter = $template->getParameter("properties_filter");
		$class = Reflection_Class::getInstanceOf($object);
		$properties = $class->accessProperties();
		$result_properties = array();
		foreach ($properties as $property_name => $property) {
			if (!$property->isStatic()) {
				if (!isset($properties_filter) || in_array($property_name, $properties_filter)) {
					$result_properties[$property_name] = new Reflection_Property_Value($property, $object);
				}
			}
		}
		$class->accessPropertiesDone();
		return $result_properties;
	}

	//------------------------------------------------------------------------ getPropertiesOutOfTabs
	/**
	 * Returns object's properties, and their display and value, but only if they are not already into a tab
	 *
	 * @param $template Html_Template
	 * @return Reflection_Property_Value[]
	 */
	public static function getPropertiesOutOfTabs(Html_Template $template)
	{
		$properties = array();
		foreach (self::getProperties($template, $template->objects) as $property_name => $property) {
			if (!$property->isStatic() && !$property->getAnnotation("group")->value) {
				$properties[$property_name] = $property;
			}
		}
		return $properties;
	}

	//---------------------------------------------------------------------------------- getRootClass
	/**
	 * Returns root class from templating tree
	 *
	 * @param $template Html_Template
	 * @return object
	 */
	public static function getRootClass(Html_Template $template)
	{
		$object = null;
		foreach (array_reverse($template->objects) as $object) {
			if (is_object($object)) {
				break;
			}
		}
		return isset($object) ? get_class($object) : null;
	}

	//--------------------------------------------------------------------------------- getRootObject
	/**
	 * Returns root object from templating tree
	 *
	 * @param $template Html_Template
	 * @return object
	 */
	public static function getRootObject(Html_Template $template)
	{
		$object = null;
		foreach (array_reverse($template->objects) as $object) {
			if (is_object($object)) {
				break;
			}
		}
		return $object;
	}

	//--------------------------------------------------------------------------------------- getSort
	/**
	 * Returns the sorted version of the objects collection
	 *
	 * @param $template Html_Template
	 * @return object[] the sorted objects collection
	 */
	public static function getSort(Html_Template $template)
	{
		if (
			is_array($collection = reset($template->objects))
			&& $collection && is_object(reset($collection))
		) {
			Collection::sort($collection);
			return $collection;
		}
		else {
			return reset($template->objects);
		}
	}

	//----------------------------------------------------------------------------- getStartingBlocks
	/**
	 * Returns the block names if current property starts one or several properties blocks
	 * If not, returns an empty string array
	 *
	 * @param $template Html_Template
	 * @return string[]
	 */
	public static function getStartingBlocks(Html_Template $template)
	{
		$blocks = array();
		foreach ($template->objects as $property) if ($property instanceof Reflection_Property) {
			$blocks = array_merge($blocks, self::getPropertyBlocks($property));
		}
		$starting_blocks = array();
		foreach ($blocks as $block) {
			if (!isset(self::$inside_blocks[$block])) {
				$starting_blocks[$block] = $block;
				self::$inside_blocks[$block] = $block;
			}
		}
		foreach (self::$inside_blocks as $block) {
			if (!isset($blocks[$block])) {
				unset(self::$inside_blocks[$block]);
			}
		}
		return $starting_blocks;
	}

	//----------------------------------------------------------------------------- getStoppingBlocks
	/**
	 * Returns the block names if current property stops one or several properties blocks
	 * If not, returns an empty string array
	 *
	 * @param $template Html_Template
	 * @return string[]
	 */
	public static function getStoppingBlocks(Html_Template $template)
	{
		if (self::$inside_blocks) {
			$array_of = null;
			$starting_objects = $template->objects;
			foreach ($template->objects as $object_key => $object) {
				if ($object instanceof Reflection_Property) {
					$array_of = $object;
				}
				elseif ($array_of instanceof Reflection_Property) {
					if (
						!is_array($object) || !is_a(reset($object), 'SAF\Framework\Reflection_Property_Value')
					) {
						$array_of = null;
					}
					else {
						$properties = $object;
						$next_property = false;
						foreach ($properties as $property) {
							if ($property->path === $array_of->path) {
								$next_property = true;
							}
							elseif ($next_property) {
								array_unshift($starting_objects, $property);
								$blocks = array();
								foreach ($starting_objects as $prop) if ($prop instanceof Reflection_Property) {
									$blocks = array_merge($blocks, self::getPropertyBlocks($prop));
								}
								break 2;
							}
						}
					}
				}
				unset($starting_objects[$object_key]);
			}
			$stopping_blocks = array();
			foreach (self::$inside_blocks as $block) {
				if (!isset($blocks[$block])) {
					$stopping_blocks[$block] = $block;
				}
			}
			return $stopping_blocks;
		}
		return array();
	}

	//---------------------------------------------------------------------------------------- getTop
	/**
	 * Returns template's top object
	 * (use it inside of loops)
	 *
	 * @param $template Html_Template
	 * @return object
	 */
	public static function getTop(Html_Template $template)
	{
		return $template->getObject();
	}

	//----------------------------------------------------------------------------- getPropertyBlocks
	/**
	 * @param $property Reflection_Property
	 * @return array[]
	 */
	private static function getPropertyBlocks(Reflection_Property $property)
	{
		$blocks = array();
		if ($property->getListAnnotation("integrated")->has("block")) {
			$blocks[$property->path] = $property->path;
		}
		foreach ($property->getListAnnotation("block")->values() as $block) {
			$blocks[$block] = $block;
		}
		return $blocks;
	}

	//--------------------------------------------------------------------------- toEditPropertyExtra
	/**
	 * Gets property extra data needed for edit widget
	 *
	 * @param $class_name string
	 * @param $property   Reflection_Property_Value|Reflection_Property|string
	 * @return mixed[] Reflection_Property $property, string $property path, mixed $value
	 */
	private static function toEditPropertyExtra($class_name, $property)
	{
		if ($property instanceof Reflection_Property_Value) {
			$property_path = $property->path;
			$value = $property->value();
		}
		elseif ($property instanceof Reflection_Property) {
			$property_path = $property->name;
			$value = "";
		}
		else {
			$property_path = $property;
			$value = "";
			$property = Reflection_Property::getInstanceOf($class_name, $property);
		}
		return array($property, $property_path, $value);
	}

}
