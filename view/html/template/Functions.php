<?php
namespace ITRocks\Framework\View\Html\Template;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Dao\Func\Expressions;
use ITRocks\Framework\Dao\Func\Now;
use ITRocks\Framework\Import\Settings\Import_Settings_Builder;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Collection;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Conditions_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Group_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Integrated_Properties;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Method;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Default_List_Data;
use ITRocks\Framework\Tools\Displayable;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\User\Access_Control;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Builder\File;
use ITRocks\Framework\View\Html\Builder\Property_Select;
use ITRocks\Framework\View\Html\Dom\Input;
use ITRocks\Framework\View\Html\Template;
use ITRocks\Framework\Widget\Condition;
use ITRocks\Framework\Widget\Data_List\Data_List_Controller;
use ITRocks\Framework\Widget\Edit\Html_Builder_Property;

/**
 * Html template functions : those which are called using {@functionName} into templates
 */
class Functions
{

	//-------------------------------------------------------------------------------- $inside_blocks
	/**
	 * Used by startingBlocks and stoppingBlocks calls
	 *
	 * @var Block[] key is the property path
	 */
	private $inside_blocks = [];

	//------------------------------------------------------------------------ displayableClassNameOf
	/**
	 * Gets the name of the source class for $object
	 *
	 * @param $object object|null
	 * @return Displayable|null
	 */
	protected function displayableClassNameOf($object)
	{
		return $object
			? new Displayable(
				is_object($object)
					? (
						($object instanceof Set)
						? Names::classToSet(Builder::current()->sourceClassName($object->element_class_name))
						: Builder::current()->sourceClassName(get_class($object))
					)
					: Builder::current()->sourceClassName($object),
				Displayable::TYPE_CLASS
			)
			: null;
	}

	//------------------------------------------------------------------------------------ escapeName
	/**
	 * @param $name string
	 * @return string
	 * @see Data_List_Controller::descapePropertyName()
	 * @see Import_Settings_Builder::buildForm()
	 */
	protected function escapeName($name)
	{
		return str_replace([DOT, '(', ')'], ['>', Q, BQ], $name);
	}

	//------------------------------------------------------------------------------ filterProperties
	/**
	 * Filter properties which @conditions values do not apply
	 *
	 * @param $object     object
	 * @param $properties Reflection_Property[]|string[] filter the list of properties
	 * @return string[]   filtered $properties
	 */
	protected function filterProperties($object, array $properties)
	{
		foreach ($properties as $key => $property) {
			if (!($property instanceof Reflection_Property)) {
				$property = new Reflection_Property(get_class($object), $property);
			}
			if (!Conditions_Annotation::of($property)->applyTo($object)) {
				unset($properties[$key]);
			}
		}
		return $properties;
	}

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
	 * If it is a built object (using Builder), always gets the source class name
	 *
	 * @param $template Template
	 * @return Displayable
	 */
	public function getClass(Template $template)
	{
		return $this->displayableClassNameOf(reset($template->objects));
	}

	//----------------------------------------------------------------------------- getConditionClass
	/**
	 * @param $template Template
	 * @return string|null
	 */
	public function getConditionClass(Template $template)
	{
		// the property path is the key for the Func\Comparison or Func\In nearest object
		$property_path = $this->getConditionLabel($template, false);
		if (Expressions::isFunction($property_path)) {
			$expression = Expressions::$current->cache[$property_path];
			$class      = is_a($expression->function, Now::class, true) ? Type::DATE_TIME : null;
		}
		else {
			$class_name = get_class($this->getRootObject($template));
			$property   = new Reflection_Property($class_name, $property_path);
			$class      = Names::classToProperty($property->getType()->getElementTypeAsString());
		}
		return $class;
	}

	//------------------------------------------------------------------------------ getConditionEdit
	/**
	 * Parses an edit field for a condition
	 *
	 * A shortcut to {@rootObject.{@key}.@edit} that enables the value of the condition
	 * You must have a Dao_Function with the property.path for the root object as key as parent object
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getConditionEdit(Template $template)
	{
		// the property path is the key for the Func\Comparison or Func\In nearest object
		$property_path = $this->getConditionLabel($template, false);
		// special functions (eg Func\Now)
		if (Expressions::isFunction($property_path)) {
			$condition = null;
			foreach ($template->objects as $condition) {
				if ($condition instanceof Condition) {
					break;
				}
			}
			$class_name    = get_class($condition);
			$expression    = Expressions::$current->cache[$property_path];
			$property_path = is_a($expression->function, Now::class, true) ? 'now' : null;
		}
		else {
			$class_name = get_class($this->getRootObject($template));
		}
		$object = reset($template->objects);
		// the stored value of a Comparison is its $than_value property value
		if ($object instanceof Comparison) {
			$object = $object->than_value;
		}
		$property = new Reflection_Property_Value($class_name, $property_path, $object, true, true);
		$output   = $this->getEditReflectionProperty($property, $property_path, true, true);
		return $output;
	}

	//----------------------------------------------------------------------------- getConditionLabel
	/**
	 * A shortcut to {@key} that enables the label of a condition (property.path or special function)
	 * You must have a Dao_Function with the property.path for the root object as key as parent object
	 *
	 * @param $template                  Template
	 * @param $resolve_expression_marker boolean
	 * @return string
	 */
	public function getConditionLabel(Template $template, $resolve_expression_marker = true)
	{
		$property_path = null;
		foreach ($template->objects as $property_path_key => $object) {
			if ($object instanceof Dao_Function) {
				$property_path = $template->var_names[$property_path_key];
				break;
			}
		}
		if ($resolve_expression_marker && Expressions::isFunction($property_path)) {
			$expression    = Expressions::$current->cache[$property_path];
			$property_path = Names::classToDisplay($expression->function);
		}
		return $property_path;
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

	//------------------------------------------------------------------------------------ getCounter
	/**
	 * Gets the next value of a counter internal to the template parser :
	 * - one counter per $class_name
	 * - reset each time $context_object changes
	 *
	 * @param $template       Template
	 * @param $class_name     string
	 * @param $context_object object
	 * @return integer
	 */
	public function getCounter(Template $template, $class_name = null, $context_object = null)
	{
		$context_class_name = $context_object ? get_class($context_object)                : null;
		$context_identifier = $context_object ? Dao::getObjectIdentifier($context_object) : null;
		arraySet($template->counters, [$context_class_name, $context_identifier, $class_name], 0);
		return ++$template->counters[$context_class_name][$context_identifier][$class_name];
	}

	//----------------------------------------------------------------------------------- getCssClass
	/**
	 * Escape strings that will be used as css class names. in HTML DOT will be replaced by '-'
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getCssClass(Template $template)
	{
		return str_replace(DOT, '-', reset($template->objects));
	}

	//--------------------------------------------------------------------------------------- getDate
	/**
	 * Return object as date
	 *
	 * @param $template Template
	 * @return Date_Time
	 */
	public function getDate(Template $template)
	{
		return new Date_Time(reset($template->objects));
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
		elseif ($object instanceof Displayable) {
			return $object->display();
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
	 * @param $template           Template
	 * @param $name               string
	 * @param $ignore_user        boolean ignore @user annotation, to disable invisible and read-only
	 * @param $can_always_be_null boolean ignore @null annotation and consider this can always be null
	 * @return string
	 */
	public function getEdit(
		Template $template, $name = null, $ignore_user = false, $can_always_be_null = false
	) {
		if (isset($name)) {
			$name = $this->escapeName($name);
		}
		$object = reset($template->objects);
		// find the first next object
		if (!($object instanceof Reflection_Property)) {
			$object        = next($template->objects);
			$property_name = reset($template->var_names);
			while (($object !== false) && !is_object($object)) {
				$object        = next($template->objects);
				$property_name = next($template->var_names);
			}
		}
		if ($object instanceof Default_List_Data) {
			return $this->getEditDefaultListData(
				$object, $template, $name, $ignore_user, $can_always_be_null
			);
		}
		if ($object instanceof Reflection_Property_Value) {
			return $this->getEditReflectionProperty(
				$object, $name, $ignore_user, $can_always_be_null
			);
		}
		if ($object instanceof Reflection_Property) {
			return $this->getEditReflectionProperty($object, $name, $ignore_user);
		}
		if (is_object($object) && isset($property_name) && is_string($property_name)) {
			$property = new Reflection_Property(get_class($object), $property_name);
			if (isset($property)) {
				return $this->getEditObjectProperty(
					$object, $property_name, $property, $template, $name, $ignore_user, $can_always_be_null
				);
			}
		}
		// default html input widget
		$input = new Input();
		$input->setAttribute('name', reset($template->objects));
		return $input;
	}

	//------------------------------------------------------------------------ getEditDefaultListData
	/**
	 * Returns an HTML edit widget for current List_Data property
	 *
	 * @param $object             Default_List_Data
	 * @param $template           Template
	 * @param $name               string
	 * @param $ignore_user        boolean ignore @user annotation, to disable invisible and read-only
	 * @param $can_always_be_null boolean ignore @null annotation and consider this can always be null
	 * @return string
	 */
	protected function getEditDefaultListData(
		Default_List_Data $object, Template $template, $name, $ignore_user, $can_always_be_null
	) {
		$class_name    = $object->element_class_name;
		$property_name = prev($template->var_names);
		list($property, $property_path, $value) = $this->toEditPropertyExtra(
			$class_name, $property_name
		);
		$property_edit             = new Html_Builder_Property($property, $value);
		$property_edit->conditions = [];
		$property_edit->name       = $name ?: $property_path;
		$property_edit->preprop    = null;
		if ($ignore_user) {
			$property_edit->readonly = false;
		}
		if ($can_always_be_null) {
			$property_edit->null = true;
		}
		return $property_edit->build();
	}

	//------------------------------------------------------------------------- getEditObjectProperty
	/**
	 * @param $object             object
	 * @param $property_name      string
	 * @param $property           Reflection_Property
	 * @param $template           Template
	 * @param $name               string
	 * @param $ignore_user        boolean
	 * @param $can_always_be_null boolean
	 * @return string
	 */
	protected function getEditObjectProperty(
		$object, $property_name, Reflection_Property $property, Template $template, $name, $ignore_user,
		$can_always_be_null
	) {
		$property_value = $property->toReflectionPropertyValue($object, true);
		if ($template->properties_prefix && !$name) {
			$prefix = isset($prefix)
				? ($prefix . '[' . reset($template->properties_prefix) . ']')
				: reset($template->properties_prefix);
			while ($next = next($template->properties_prefix)) {
				if ((strpos($next, BS) !== false) && class_exists($next)) {
					$next = Names::classToDisplay($next);
				}
				else {
					$next = $this->escapeName($next);
				}
				$prefix .= '[' . $next . ']';
			}
			$property_edit = new Html_Builder_Property($property_value, null, $prefix);
		}
		else {
			$property_edit       = new Html_Builder_Property($property_value);
			$property_edit->name = $name ?: $property_name;
		}
		$property_edit->conditions = [];
		if ($can_always_be_null) {
			$property_edit->null = true;
		}
		if ($ignore_user) {
			$property_edit->readonly = false;
		}
		return $property_edit->build();
	}

	//--------------------------------------------------------------------- getEditReflectionProperty
	/**
	 * Returns an HTML edit widget for current Reflection_Property|Reflection_Property_Value object
	 *
	 * @param $property           Reflection_Property
	 * @param $name               string
	 * @param $ignore_user        boolean ignore @user annotation, to disable invisible and read-only
	 * @param $can_always_be_null boolean ignore @null annotation and consider this can always be null
	 * @return string
	 */
	protected function getEditReflectionProperty(
		Reflection_Property $property, $name, $ignore_user, $can_always_be_null = false
	) {
		$property_edit             = new Html_Builder_Property($property);
		$property_edit->conditions = [];
		$property_edit->name       = $name ?: $property->path;
		$property_edit->preprop    = null;
		if ($ignore_user) {
			$property_edit->readonly = false;
		}
		if ($can_always_be_null) {
			$property_edit->null = true;
		}
		return $property_edit->build();
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
	 * @param $template Template
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
		return $this->escapeName(reset($template->objects));
	}

	//------------------------------------------------------------------------------------- getExpand
	/**
	 * Returns an expanded list of properties. Source element must be a list of Reflection_Property
	 *
	 * @param $template Template
	 * @return Reflection_Property[]
	 */
	public function getExpand(Template $template)
	{
		/** @var $property Reflection_Property */
		$property = reset($template->objects);
		$expanded = [];
		$expanded = $this->visibleProperties((new Integrated_Properties)->expandUsingProperty(
			$expanded, $property, $template->getParentObject($property->class)
		));
		if ($expanded) {
			/** @var $first_property Reflection_Property_Value PhpStorm should see this, but no */
			$first_property = reset($expanded);
			$object         = $first_property->getObject(true);
			$expanded       = $this->filterProperties($object, $expanded);
			$properties     = $expanded;
		}
		else {
			$properties = [$property];
		}
		if ($expand_property_path = $template->getParameter(Parameter::EXPAND_PROPERTY_PATH)) {
			foreach ($properties as $property) {
				// view_path for html name must include the 'expand property path'
				if ($property instanceof Reflection_Property_Value) {
					$property->view_path = $expand_property_path . DOT . $property->path;
				}
				// for Reflection_Property : we can "break" $property->path with it : used for html name
				else {
					$property->path = $expand_property_path . DOT . $property->path;
				}
				if (($property instanceof Reflection_Property_Value) && !$property->display) {
					$property->display = Loc::tr(rLastParse($property->aliased_path, DOT . DOT, 1, true));
				}
			}
		}
		return $properties;
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

	//-------------------------------------------------------------------------------------- getField
	/**
	 * Return the current data as a field
	 *
	 * @param $template Template
	 * @return mixed
	 */
	public function getField(Template $template)
	{
		return reset($template->objects);
	}

	//--------------------------------------------------------------------------------------- getFile
	/**
	 * Returns a Builder\File object for the current File value
	 *
	 * @param $template Template
	 * @return File
	 */
	public function getFile(Template $template)
	{
		return new File(reset($template->objects));
	}

	//--------------------------------------------------------------------------- getFilterProperties
	/**
	 * Filter a property or a property list that should not be displayed.
	 * The top object of the template must be a Reflection_Property[], or it will returned as null
	 *
	 * @param $template Template
	 * @return Reflection_Property[]
	 */
	public function getFilterProperties(Template $template)
	{
		/** @var $properties Reflection_Property[] */
		$properties        = reset($template->objects);
		$properties_filter = $template->getParameter(Parameter::PROPERTIES_FILTER);
		// properties array
		if (is_array($properties)) {
			if ($properties_filter) {
				foreach ($properties as $key => $property) {
					if (!in_array($property->name, $properties_filter)) {
						unset($properties[$key]);
					}
				}
			}
			$object     = $this->getObject($template);
			$properties = $this->filterProperties($object, $properties);
		}
		// property
		elseif ($properties instanceof Reflection_Property) {
			$property =& $properties;
			if ($properties_filter && !in_array($property->name, $properties_filter)) {
				$property = null;
			}
			elseif ($property instanceof Reflection_Property_Value) {
				$object = $property->getObject();
				if (!$this->filterProperties($object, [$property])) {
					$property = null;
				}
			}
		}
		return $properties;
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
	 * (useful for conditions on arrays)
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getHas(Template $template)
	{
		$object = reset($template->objects);
		return !empty($object);
	}

	//-------------------------------------------------------------------------------- getHasAccessTo
	/**
	 * Check if current user access to the given feature.
	 *
	 * @param $template   Template The current template object
	 * @param $feature    string   The feature to check access to
	 * @param $class_name string   The object class concerned by the feature (optional).
	 *                             By default, the current context class is used.
	 * @return boolean
	 */
	public function getHasAccessTo(Template $template, $feature, $class_name = null)
	{
		if (!$class_name) {
			$class_name = (string)$this->getClass($template);
		}
		return Access_Control::get()->hasAccessTo([$class_name, $feature]);
	}

	//-------------------------------------------------------------------------------------- getImage
	/**
	 * Returns a <img src=""> with a link to the image of the current File property
	 *
	 * @param $template Template
	 * @param $width    integer
	 * @param $height   integer
	 * @return string
	 */
	public function getImage(Template $template, $width = null, $height = null)
	{
		return $this->getFile($template)->buildImage($width, $height);
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

	//--------------------------------------------------------------------------------------- getJson
	/**
	 * Encode using json
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getJson(Template $template)
	{
		return json_encode(reset($template->objects));
	}

	//---------------------------------------------------------------------------------------- getKey
	/**
	 * Returns the current key of the current element of the currently read array
	 *
	 * @param $template Template
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

	//-------------------------------------------------------------------------------------- getLines
	/**
	 * Returns lines from a text
	 *
	 * @param $template Template
	 * @return string[]
	 */
	public function getLines(Template $template)
	{
		return explode(LF, strval($template->getObject()));
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
	 * @return mixed
	 */
	public function getLoc(Template $template)
	{
		reset($template->var_names);
		foreach ($template->objects as $object) {
			if (is_object($object)) {
				if ($object instanceof Date_Time) {
					do {
						$parent = next($template->objects);
						if ($parent instanceof Date_Time) {
							next($template->var_names);
						}
					} while ($parent instanceof Date_Time);
					if (is_object($parent) && property_exists($parent, current($template->var_names))) {
						// call propertyToLocale to apply @show_seconds
						return Loc::propertyToLocale(
							new Reflection_Property(get_class($parent), current($template->var_names)),
							$object
						);
					}
					return Loc::dateToLocale($object);
				}
				else {
					$property_name = reset($template->var_names);
					if (method_exists(get_class($object), $property_name)) {
						$method = new Reflection_Method(get_class($object), $property_name);
						return Loc::methodToLocale($method, reset($template->objects));
					}
					$property = new Reflection_Property(get_class($object), $property_name);
					return Loc::propertyToLocale($property, reset($template->objects));
				}
			}
			next($template->var_names);
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

	//----------------------------------------------------------------------------------- getTemplate
	/**
	 * Allow to navigate through the template object
	 *
	 * @example {@template.css}
	 * @param $template Template
	 * @return Template
	 */
	public function getTemplate(Template $template)
	{
		return $template;
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
	 * Returns nearest object from template objects stack
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
		$object             = reset($template->objects);
		$properties_filter  = $template->getParameter(Parameter::PROPERTIES_FILTER);
		$properties_title   = $template->getParameter(Parameter::PROPERTIES_TITLE);
		$properties_tooltip = $template->getParameter(Parameter::PROPERTIES_TOOLTIP);

		/** @noinspection PhpUnhandledExceptionInspection Class of an object is always valid */
		$class             = new Reflection_Class(get_class($object));
		$result_properties = [];

		if ($properties_filter) {
			$properties = [];
			foreach ($properties_filter as $property_path) {
				$properties[$property_path] = new Reflection_Property($class->name, $property_path);
			}
		}
		else {
			$properties = $class->accessProperties([Reflection_Class::T_SORT, T_EXTENDS, T_USE]);
		}

		foreach ($properties as $property_path => $property) {
			if (!$property->isStatic()) {
				$property = new Reflection_Property_Value(
					$class->name, $property->path, $object, false, true
				);
				if ($this->isPropertyVisible($property)) {
					if (isset($properties_title) && isset($properties_title[$property_path])) {
						$property->display = $properties_title[$property_path];
					}
					if (isset($properties_tooltip) && isset($properties_tooltip[$property_path])) {
						$property->tooltip = $properties_tooltip[$property_path];
					}
					$result_properties[$property_path] = $property;
					if (strpos($property_path, DOT)) {
						Group_Annotation::local($property)->replaceByClass($class, $property_path);
					}
				}
			}
		}

		/** @var $result_properties Reflection_Property_Value[] */
		$result_properties = Replaces_Annotations::removeReplacedProperties($result_properties);
		return $result_properties;
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
			if (!Group_Annotation::of($property)->value) {
				$properties[$property_name] = $property;
			}
		}
		return $properties;
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

	//----------------------------------------------------------------------------- getPropertyBlocks
	/**
	 * @param $property Reflection_Property
	 * @return Block[]
	 */
	protected function getPropertyBlocks(Reflection_Property $property)
	{
		$blocks     = [];
		$integrated = $property->getListAnnotation(Integrated_Annotation::ANNOTATION);
		if ($integrated->has(Integrated_Annotation::BLOCK)) {
			$conditions = Conditions_Annotation::of($property);
			$data = $conditions->values()
				? ['conditions' => $conditions->asHtmlAttributeValue(), 'name' => $property->name]
				: [];
			$blocks[$property->path] = new Block($property->path, $data);
		}
		foreach ($property->getListAnnotation(Annotation::BLOCK)->values() as $block) {
			$blocks[$block] = new Block($block);
		}
		return $blocks;
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
	 * Returns root class from template objects stack
	 *
	 * @param $template Template
	 * @return Displayable
	 */
	public function getRootClass(Template $template)
	{
		return $this->displayableClassNameOf($this->getRootObject($template));
	}

	//--------------------------------------------------------------------------------- getRootObject
	/**
	 * Returns root object from template objects stack
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
	 * @return Block[]
	 */
	public function getStartingBlocks(Template $template)
	{
		$blocks = [];
		foreach ($template->objects as $property) if ($property instanceof Reflection_Property) {
			$blocks = array_merge($blocks, $this->getPropertyBlocks($property));
		}
		$starting_blocks = [];
		foreach ($blocks as $block_name => $block) {
			if (!isset($this->inside_blocks[$block_name])) {
				$starting_blocks[$block_name]     = $block;
				$this->inside_blocks[$block_name] = $block;
			}
		}
		foreach ($this->inside_blocks as $block_name => $block) {
			if (!isset($blocks[$block_name])) {
				unset($this->inside_blocks[$block_name]);
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
	 * @return Block[]
	 */
	public function getStoppingBlocks(Template $template)
	{
		if ($this->inside_blocks) {
			$array_of         = null;
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
						$properties    = $object;
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
			foreach ($this->inside_blocks as $block_name => $block) {
				if (!isset($blocks[$block_name])) {
					$stopping_blocks[$block_name] = $block;
				}
			}
			return $stopping_blocks;
		}
		return [];
	}

	//--------------------------------------------------------------------------------------- getTime
	/**
	 * @param $template Template The first object must be a Date_Time or an ISO date-time
	 * @return string '13:12:20' or an empty string if was not an ISO date-time string
	 */
	public function getTime(Template $template)
	{
		/** @var $object Date_Time */
		$object = $template->getObject();
		return is_string($object)
			? substr($object, (strpos($object, SP) ?: (strlen($object) - 1)) + 1)
			: $object->format('H:i:s');
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
	 * @param $template Template
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

	//--------------------------------------------------------------------------------------- getVoid
	/**
	 * Returns true if the object is void ie if its string value has no length
	 *
	 * A value is void if :
	 * - null
	 * - empty string
	 *
	 * It is not void if its numeric value is 0 or 0.00
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getVoid(Template $template)
	{
		return !strlen(reset($template->objects));
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

	//----------------------------------------------------------------------------- isPropertyVisible
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isPropertyVisible(Reflection_Property $property)
	{
		return $property->isVisible()
			&& !User_Annotation::of($property)->has(User_Annotation::HIDE_OUTPUT);
	}

	//--------------------------------------------------------------------------- toEditPropertyExtra
	/**
	 * Gets property extra data needed for edit widget
	 *
	 * @param $class_name string
	 * @param $property   Reflection_Property_Value|Reflection_Property|string
	 * @return mixed[] Reflection_Property $property, string $property path, mixed $value
	 */
	protected function toEditPropertyExtra($class_name, $property)
	{
		if ($property instanceof Reflection_Property_Value) {
			$property_path = $property->path;
			$value         = $property->value();
		}
		elseif ($property instanceof Reflection_Property) {
			$property_path = $property->name;
			$value         = '';
		}
		else {
			$property_path = $property;
			$value         = '';
			$property      = new Reflection_Property($class_name, $property);
		}
		return [$property, $property_path, $value];
	}

	//----------------------------------------------------------------------------- visibleProperties
	/**
	 * @param $properties Reflection_Property[]
	 * @return Reflection_Property[]
	 */
	protected function visibleProperties(array $properties)
	{
		foreach ($properties as $key => $property) {
			if (!$this->isPropertyVisible($property)) {
				unset($properties[$key]);
			}
		}
		return $properties;
	}

}
