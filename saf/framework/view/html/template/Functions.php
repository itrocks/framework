<?php
namespace SAF\Framework\View\Html\Template;

use SAF\Framework\Controller\Parameter;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Mapper\Collection;
use SAF\Framework\Reflection\Annotation\Property\User_Annotation;
use SAF\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use SAF\Framework\Reflection\Integrated_Properties;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Method;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Session;
use SAF\Framework\Tools\Date_Time;
use SAF\Framework\Tools\Default_List_Data;
use SAF\Framework\Tools\Displayable;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Set;
use SAF\Framework\View;
use SAF\Framework\View\Html\Builder\Property_Select;
use SAF\Framework\View\Html\Dom\Input;
use SAF\Framework\View\Html\Template;
use SAF\Framework\Widget\Edit\Html_Builder_Property;

/**
 * Html template functions : those which are called using {@functionName} into templates
 */
class Functions
{

	//-------------------------------------------------------------------------------- $inside_blocks
	/**
	 * Used by startingBlocks and stoppingBlocks calls
	 *
	 * @var string[] key equals value
	 */
	private $inside_blocks = [];

	//-------------------------------------------------------------------------------- getApplication
	/**
	 * Returns application name
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getApplication(
		/** @noinspection PhpUnusedParameterInspection */ Template $template
	) {
		return new Displayable(
			Session::current()->getApplicationName(), Displayable::TYPE_CLASS
		);
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Returns object's class name
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getClass(Template $template)
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
	 * @param $template Template
	 * @return integer
	 */
	public function getCount(Template $template)
	{
		return count(reset($template->objects));
	}

	//------------------------------------------------------------------------------------ getDisplay
	/**
	 * Return object's display
	 *
	 * @param $template          Template
	 * @param $display_full_path boolean If object is a property, returns 'the.full.property.path'
	 * @return string
	 */
	public function getDisplay(Template $template, $display_full_path = false)
	{
		$object = reset($template->objects);
		if ($object instanceof Reflection_Property) {
			return Names::propertyToDisplay($display_full_path ? $object->path : $object->name);
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
	 * @param $template          Template
	 * @param $name               string
	 * @param $ignore_user        boolean ignore @user annotation, to disable invisible and read-only
	 * @param $can_always_be_null boolean ignore @null annotation and consider this can always be null
	 * @return string
	 */
	public function getEdit(
		Template $template, $name = null, $ignore_user = false, $can_always_be_null = false
	) {
		if (isset($name)) {
			$name = str_replace(DOT, '>', $name);
		}
		$object = reset($template->objects);
		// find the first next object
		if (!($object instanceof Reflection_Property)) {
			$object = next($template->objects);
			$property_name = reset($template->var_names);
			while (($object !== false) && !is_object($object)) {
				$object        = next($template->objects);
				$property_name = next($template->var_names);
			}
		}
		if ($object instanceof Default_List_Data) {
			$class_name = $object->element_class_name;
			$property_name = prev($template->var_names);
			list($property, $property_path, $value) = $this->toEditPropertyExtra(
				$class_name, $property_name
			);
			$property_edit = new Html_Builder_Property($property, $value);
			$property_edit->name = $name ?: $property_path;
			$property_edit->preprop = null;
			if ($ignore_user) {
				$property_edit->readonly = false;
			}
			if ($can_always_be_null) {
				$property_edit->null = true;
			}
			return $property_edit->build();
		}
		if ($object instanceof Reflection_Property_Value) {
			$property_edit = new Html_Builder_Property($object, $object->value());
			$property_edit->name = $name ?: $object->path;
			$property_edit->preprop = null;
			if ($ignore_user) {
				$property_edit->readonly = false;
			}
			if ($can_always_be_null) {
				$property_edit->null = true;
			}
			return $property_edit->build();
		}
		if ($object instanceof Reflection_Property) {
			$property_edit = new Html_Builder_Property($object);
			$property_edit->name = $name ?: $object->path;
			$property_edit->preprop = null;
			if ($ignore_user) {
				$property_edit->readonly = false;
			}
			return $property_edit->build();
		}
		if (is_object($object) && isset($property_name) && is_string($property_name)) {
			$property = new Reflection_Property(get_class($object), $property_name);
			if (isset($property)) {
				if ($template->preprops && !$name) {
					$preprop = isset($preprop)
						? ($preprop . '[' . reset($template->preprops) . ']')
						: reset($template->preprops);
					while ($next = next($template->preprops)) {
						if ((strpos($next, BS) !== false) && class_exists($next)) {
							$next = Names::classToDisplay($next);
						}
						else {
							$next = str_replace(DOT, '>', $next);
						}
						$preprop .= '[' . $next . ']';
					}
					$property_edit = new Html_Builder_Property(
						$property, $property->getValue($object), $preprop
					);
				}
				else {
					$property_edit = new Html_Builder_Property($property, $property->getValue($object));
					$property_edit->name = $name ?: $property_name;
				}
				if ($ignore_user) {
					$property_edit->readonly = false;
				}
				if ($can_always_be_null) {
					$property_edit->null = true;
				}
				return $property_edit->build();
			}
		}
		// default html input widget
		$input = new Input();
		$input->setAttribute('name', reset($template->objects));
		return $input;
	}

	//-------------------------------------------------------------------------------------- getEmpty
	/**
	 * Returns true if the object is empty
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getEmpty(Template $template)
	{
		return empty(reset($template->objects));
	}

	//---------------------------------------------------------------------------- getEndWithMultiple
	/**
	 * Multiple properties come last
	 *
	 * @param Template $template
	 * @return string
	 */
	public function getEndWithMultiple(Template $template)
	{
		/** @var  $properties Reflection_Property[] */
		$properties = reset($template->objects);
		if (is_array($properties)) {
			foreach ($properties as $key => $property) {
				if ($property->getType()->isMultiple()) {
					unset($properties[$key]);
					$properties[$key] = $property;
				}
			}
		}
		return $properties;
	}

	//--------------------------------------------------------------------------------- getEscapeName
	/**
	 * Escape strings that will be used as form names. in HTML DOT will be replaced by '>' as PHP
	 * does not like variables named 'a.b.c'
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getEscapeName(Template $template)
	{
		return str_replace(DOT, '>', reset($template->objects));
	}

	//------------------------------------------------------------------------------------- getExpand
	/**
	 * Returns an expanded list of properties. Source element must be a list of Reflection_Property
	 *
	 * @param $template Template
	 * @return Reflection_Property
	 */
	public function getExpand(Template $template)
	{
		$property = reset($template->objects);
		$expanded = Integrated_Properties::expandUsingProperty(
			$expanded, $property, $template->getParentObject($property->class)
		);
		$result = $expanded ? $expanded : [$property];
		if ($expand_property_path = $template->getParameter(Parameter::EXPAND_PROPERTY_PATH)) {
			foreach ($result as $property) {
				$property->path = $expand_property_path . DOT . $property->path;
				if (($property instanceof Reflection_Property_Value) && !$property->display) {
					$property->display = rLastParse($property->path, DOT . DOT, 1, true);
				}
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * Returns template's feature method name
	 *
	 * @param $template Template
	 * @return Displayable
	 */
	public function getFeature(Template $template)
	{
		return new Displayable($template->getFeature(), Displayable::TYPE_METHOD);
	}

	//-------------------------------------------------------------------------------------- getFirst
	/**
	 * Returns the first current array element
	 *
	 * @param $template Template
	 * @return mixed
	 */
	public function getFirst(Template $template)
	{
		foreach ($template->objects as $array) {
			if (is_array($array)) {
				return reset($array);
			}
		}
		return null;
	}

	//---------------------------------------------------------------------------------------- getHas
	/**
	 * Returns true if the element is not empty
	 * (usefull for conditions on arrays)
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getHas(Template $template)
	{
		$object = reset($template->objects);
		return !empty($object);
	}

	//------------------------------------------------------------------------------------ getIsFirst
	/**
	 * Returns true if the current array element is the first one
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getIsFirst(Template $template)
	{
		$var_name = null;
		foreach ($template->objects as $array) {
			if (is_array($array)) {
				reset($array);
				return (key($array) == $var_name);
			}
			$var_name = isset($var_name) ? next($template->var_names) : reset($template->var_names);
		}
		return null;
	}

	//------------------------------------------------------------------------------------- getIsLast
	/**
	 * Returns true if the current array element is the last one
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getIsLast(Template $template)
	{
		$var_name = null;
		foreach ($template->objects as $array) {
			if (is_array($array)) {
				end($array);
				return (key($array) == $var_name);
			}
			$var_name = isset($var_name) ? next($template->var_names) : reset($template->var_names);
		}
		return null;
	}

	//---------------------------------------------------------------------------------------- getKey
	/**
	 * Returns the current key of the current element of the currently read array
	 *
	 * @param Template $template
	 * @return string|integer
	 */
	public function getKey(Template $template)
	{
		foreach ($template->objects as $key => $array) {
			if (is_array($array) && $key) {
				return $template->var_names[$key - 1];
			}
		}
		return null;
	}

	//--------------------------------------------------------------------------------------- getLink
	/**
	 * Returns a link to the nearest object
	 *
	 * @param $template Template
	 * @param $feature  string
	 * @return string
	 */
	public function getLink(Template $template, $feature = null)
	{
		foreach ($template->objects as $object) {
			if (is_object($object)) {
				return View::link($object, $feature);
			}
		}
		return null;
	}

	//---------------------------------------------------------------------------------------- getLoc
	/**
	 * Returns a value with application of current locales
	 *
	 * @param $template Template
	 * @return object
	 */
	public function getLoc(Template $template)
	{
		foreach ($template->objects as $object) {
			if (is_object($object)) {
				if ($object instanceof Date_Time) {
					return Loc::dateToLocale($object);
				}
				else {
					$property_name = reset($template->var_names);
					if (method_exists(get_class($object), $property_name)) {
						$method = new Reflection_Method(get_class($object), $property_name);
						return Loc::methodToLocale($method, reset($template->objects));
					}
					else {
						$property = new Reflection_Property(get_class($object), $property_name);
						return Loc::propertyToLocale($property, reset($template->objects));
					}
				}
				break;
			}
		}
		return reset($object);
	}

	//--------------------------------------------------------------------------------------- getNull
	/**
	 * Returns true if the object is null or empty string
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getNull(Template $template)
	{
		return reset($template->objects) === null;
	}

	//------------------------------------------------------------------------------------ getNumeric
	/**
	 * Returns true if the object is numeric
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getNumeric(Template $template)
	{
		return is_numeric(reset($template->objects));
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Returns nearest object from templating tree
	 *
	 * After this call, current($template->var_names) will give you the var name of the object
	 *
	 * @param $template Template
	 * @return object
	 */
	public function getObject(Template $template)
	{
		$object = null;
		reset($template->var_names);
		foreach ($template->objects as $object) {
			if (is_object($object)) {
				break;
			}
			next($template->var_names);
		}
		return $object;
	}

	//-------------------------------------------------------------------------------------- getParse
	/**
	 * Parse vars from the string value
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getParse(Template $template)
	{
		return $template->parseVars(
			str_replace(['&#123;', '&#125;'], ['{', '}'], reset($template->objects))
		);
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns object's properties, and their display and value
	 *
	 * @param $template Template
	 * @return Reflection_Property_Value[]
	 */
	public function getProperties(Template $template)
	{
		$object = reset($template->objects);
		$properties_filter = $template->getParameter(Parameter::PROPERTIES_FILTER);
		$class = new Reflection_Class(get_class($object));
		$result_properties = [];
		foreach ($class->accessProperties() as $property_name => $property) {
			if (
				!$property->isStatic()
				&& !$property->getListAnnotation('user')->has(User_Annotation::INVISIBLE)
			) {
				if (!isset($properties_filter) || in_array($property_name, $properties_filter)) {
					$property = new Reflection_Property_Value(
						$property->class, $property->name, $object, false, true
					);
					$property->final_class = $class->name;
					$result_properties[$property_name] = $property;
				}
			}
		}
		return Replaces_Annotations::removeReplacedProperties($result_properties);
	}

	//------------------------------------------------------------------------ getPropertiesOutOfTabs
	/**
	 * Returns object's properties, and their display and value, but only if they are not already into a tab
	 *
	 * @param $template Template
	 * @return Reflection_Property_Value[]
	 */
	public function getPropertiesOutOfTabs(Template $template)
	{
		$properties = [];
		foreach ($this->getProperties($template) as $property_name => $property) {
			if (!$property->getAnnotation('group')->value) {
				$properties[$property_name] = $property;
			}
		}
		return $properties;
	}

	//----------------------------------------------------------------------------- getPropertyBlocks
	/**
	 * @param $property Reflection_Property
	 * @return array[]
	 */
	private function getPropertyBlocks(Reflection_Property $property)
	{
		$blocks = [];
		if ($property->getListAnnotation('integrated')->has('block')) {
			$blocks[$property->path] = $property->path;
		}
		foreach ($property->getListAnnotation('block')->values() as $block) {
			$blocks[$block] = $block;
		}
		return $blocks;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @param $template Template
	 * @param $name     string
	 * @return string
	 */
	public function getProperty(Template $template, $name = null)
	{
		foreach ($template->objects as $object) {
			if (is_object($object)) {
				return new Reflection_Property_Value(get_class($object), $name, $object, false, true);
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------- getPropertySelect
	/**
	 * @param $template Template
	 * @param $name     string
	 * @return string
	 */
	public function getPropertySelect(Template $template, $name = null)
	{
		foreach ($template->objects as $property) {
			if ($property instanceof Reflection_Property) {
				return (new Property_Select($property, $name))->build();
			}
		}
		return null;
	}

	//---------------------------------------------------------------------------------- getRootClass
	/**
	 * Returns root class from templating tree
	 *
	 * @param $template Template
	 * @return object
	 */
	public function getRootClass(Template $template)
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
	 * @param $template Template
	 * @return object
	 */
	public function getRootObject(Template $template)
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
	 * @param $template Template
	 * @return object[] the sorted objects collection
	 */
	public function getSort(Template $template)
	{
		if (
			is_array($collection = reset($template->objects))
			&& $collection && is_object(reset($collection))
		) {
			return (new Collection($collection))->sort();
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
	 * @param $template Template
	 * @return string[]
	 */
	public function getStartingBlocks(Template $template)
	{
		$blocks = [];
		foreach ($template->objects as $property) if ($property instanceof Reflection_Property) {
			$blocks = array_merge($blocks, $this->getPropertyBlocks($property));
		}
		$starting_blocks = [];
		foreach ($blocks as $block) {
			if (!isset($this->inside_blocks[$block])) {
				$starting_blocks[$block] = $block;
				$this->inside_blocks[$block] = $block;
			}
		}
		foreach ($this->inside_blocks as $block) {
			if (!isset($blocks[$block])) {
				unset($this->inside_blocks[$block]);
			}
		}
		return $starting_blocks;
	}

	//----------------------------------------------------------------------------- getStoppingBlocks
	/**
	 * Returns the block names if current property stops one or several properties blocks
	 * If not, returns an empty string array
	 *
	 * @param $template Template
	 * @return string[]
	 */
	public function getStoppingBlocks(Template $template)
	{
		if ($this->inside_blocks) {
			$array_of = null;
			$starting_objects = $template->objects;
			foreach ($template->objects as $object_key => $object) {
				if ($object instanceof Reflection_Property) {
					$array_of = $object;
				}
				elseif ($array_of instanceof Reflection_Property) {
					if (
						!is_array($object) || !is_a(reset($object), Reflection_Property_Value::class)
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
								$blocks = [];
								foreach ($starting_objects as $prop) if ($prop instanceof Reflection_Property) {
									$blocks = array_merge($blocks, $this->getPropertyBlocks($prop));
								}
								break 2;
							}
						}
					}
				}
				unset($starting_objects[$object_key]);
			}
			$stopping_blocks = [];
			foreach ($this->inside_blocks as $block) {
				if (!isset($blocks[$block])) {
					$stopping_blocks[$block] = $block;
				}
			}
			return $stopping_blocks;
		}
		return [];
	}

	//---------------------------------------------------------------------------------------- getTop
	/**
	 * Returns template's top object
	 * (use it inside of loops)
	 *
	 * @param $template Template
	 * @return object
	 */
	public function getTop(Template $template)
	{
		return $template->getObject();
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Returns the current value of the current element of the currently read array
	 *
	 * @param Template $template
	 * @return string|integer
	 */
	public function getValue(Template $template)
	{
		foreach ($template->objects as $key => $array) {
			if (is_array($array) && $key) {
				return $template->objects[$key - 1];
			}
		}
		return null;
	}

	//--------------------------------------------------------------------------------------- getZero
	/**
	 * Returns true if the object is null or empty string
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getZero(Template $template)
	{
		return strval(reset($template->objects)) === '0';
	}

	//--------------------------------------------------------------------------- toEditPropertyExtra
	/**
	 * Gets property extra data needed for edit widget
	 *
	 * @param $class_name string
	 * @param $property   Reflection_Property_Value|Reflection_Property|string
	 * @return mixed[] Reflection_Property $property, string $property path, mixed $value
	 */
	private function toEditPropertyExtra($class_name, $property)
	{
		if ($property instanceof Reflection_Property_Value) {
			$property_path = $property->path;
			$value = $property->value();
		}
		elseif ($property instanceof Reflection_Property) {
			$property_path = $property->name;
			$value = '';
		}
		else {
			$property_path = $property;
			$value = '';
			$property = new Reflection_Property($class_name, $property);
		}
		return [$property, $property_path, $value];
	}

}
