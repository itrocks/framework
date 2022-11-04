<?php /** @noinspection PhpUnused into HTML templates */
namespace ITRocks\Framework\View\Html\Template;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func\Comparison;
use ITRocks\Framework\Dao\Func\Dao_Function;
use ITRocks\Framework\Dao\Func\Expressions;
use ITRocks\Framework\Dao\Func\Now;
use ITRocks\Framework\Feature\Condition;
use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\Feature\Validate\Property\Values_Annotation;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Collection;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Conditions_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Group_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Integrated_Properties;
use ITRocks\Framework\Reflection\Interfaces;
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
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\User\Access_Control;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Builder\File;
use ITRocks\Framework\View\Html\Builder\Property_Select;
use ITRocks\Framework\View\Html\Dom\Input;
use ITRocks\Framework\View\Html\Template;
use ReflectionException;

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
	private array $inside_blocks = [];

	//------------------------------------------------------------------------ displayableClassNameOf
	/**
	 * Gets the name of the source class for $object
	 *
	 * @param $object object|string|null
	 * @return ?Displayable
	 */
	protected function displayableClassNameOf(object|string|null $object) : ?Displayable
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
	 * @see List_\Controller::descapePropertyName()
	 * @see Import_Settings_Builder::buildForm()
	 */
	protected function escapeName(string $name) : string
	{
		return str_replace([DOT, '(', ')'], ['>', Q, BQ], $name);
	}

	//------------------------------------------------------------------------------ filterProperties
	/**
	 * Filter properties which conditions annotation values do not apply
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object     object
	 * @param $properties Reflection_Property[]|string[] filter the list of properties
	 * @return string[]   filtered $properties
	 */
	protected function filterProperties(object $object, array $properties) : array
	{
		foreach ($properties as $key => $property) {
			if (!($property instanceof Reflection_Property)) {
				/** @noinspection PhpUnhandledExceptionInspection object */
				$property = new Reflection_Property($object, $property);
			}
			if (!Conditions_Annotation::of($property)->applyTo($object)) {
				unset($properties[$key]);
			}
		}
		return $properties;
	}

	//------------------------------------------------------------------------------- getAbsoluteLink
	/**
	 * @param $template Template
	 * @param $feature string
	 * @return string
	 */
	public function getAbsoluteLink(Template $template, string $feature = '') : string
	{
		return Paths::getUrl() . $this->getLink($template, $feature);
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
	) : string
	{
		return new Displayable(Session::current()->getApplicationName());
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Returns object's class name
	 * If it is a built object (using Builder), always gets the source class name
	 *
	 * @param $template Template
	 * @return ?Displayable
	 */
	public function getClass(Template $template) : ?Displayable
	{
		return $this->displayableClassNameOf(reset($template->objects));
	}

	//----------------------------------------------------------------------------- getConditionClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $template Template
	 * @return string
	 */
	public function getConditionClass(Template $template) : string
	{
		// the property path is the key for the Func\Comparison or Func\In nearest object
		$property_path = $this->getConditionLabel($template, false);
		if (Expressions::isFunction($property_path)) {
			$expression = Expressions::$current->cache[$property_path];
			$class      = is_a($expression->function, Now::class, true) ? Type::DATE_TIME : null;
		}
		else {
			$root_object = $this->getRootObject($template);
			/** @noinspection PhpUnhandledExceptionInspection object, $property_path is controlled */
			$property = new Reflection_Property($root_object, $property_path);
			$class    = Names::classToProperty($property->getType()->getElementTypeAsString());
		}
		return $class;
	}

	//------------------------------------------------------------------------------ getConditionEdit
	/**
	 * Parses an edit field for a condition
	 *
	 * A shortcut to "{@rootObject.{@key}.@edit}" that enables the value of the condition
	 * You must have a Dao_Function with the property.path for the root object as key as parent object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $template Template
	 * @return string
	 */
	public function getConditionEdit(Template $template) : string
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
			$condition_object = $condition;
			$expression       = Expressions::$current->cache[$property_path];
			$property_path    = is_a($expression->function, Now::class, true) ? 'now' : '';
		}
		else {
			$condition_object = $this->getRootObject($template);
		}
		$object = reset($template->objects);
		// the stored value of a Comparison is its $than_value property value
		if ($object instanceof Comparison) {
			$object = $object->than_value;
		}
		/** @noinspection PhpUnhandledExceptionInspection object, controlled $property_path */
		$property = new Reflection_Property_Value(
			$condition_object, $property_path, $object, true, true
		);
		return $this->getEditReflectionProperty($property, $property_path, true, true);
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
	public function getConditionLabel(
		Template $template, bool $resolve_expression_marker = true
	) : string
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
	public function getCount(Template $template) : int
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
	 * @param $class_name     string|null
	 * @param $context_object object|null
	 * @return integer
	 */
	public function getCounter(
		Template $template, string $class_name = null, object $context_object = null
	) : int
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
	public function getCssClass(Template $template) : string
	{
		return str_replace(DOT, '-', reset($template->objects));
	}

	//--------------------------------------------------------------------------------------- getDate
	/**
	 * Return object as date
	 *
	 * @param $template Template
	 * @return Date_Time
	 * @throws Exception
	 */
	public function getDate(Template $template) : Date_Time
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
	public function getDisplay(Template $template, bool $display_full_path = false) : string
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
		return strval($object);
	}

	//----------------------------------------------------------------------------------- getDisplays
	/**
	 * Return object's displays
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getDisplays(Template $template) : string
	{
		$object = reset($template->objects);
		if ($object instanceof Reflection_Property) {
			return Names::singleToSet(Names::propertyToDisplay($object->name));
		}
		elseif ($object instanceof Reflection_Class) {
			return Names::classToDisplays($object->name);
		}
		elseif ($object instanceof Reflection_Method) {
			return Names::singleToSet(Names::methodToDisplay($object->name));
		}
		elseif ($object instanceof Displayable) {
			return Names::singleToSet($object->display());
		}
		elseif (is_object($object)) {
			return Names::singleToSet(
				(new Displayable(get_class($object), Displayable::TYPE_CLASS))->display()
			);
		}
		return Names::singleToSet($object);
	}

	//--------------------------------------------------------------------------------------- getDump
	/**
	 * @param $template Template
	 * @return string
	 */
	public function getDump(Template $template) : string
	{
		return print_r(reset($template->objects), true);
	}

	//--------------------------------------------------------------------------------------- getEdit
	/**
	 * Returns an HTML edit widget for current property or List_Data property
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $template           Template
	 * @param $name               string
	 * @param $ignore_user        boolean ignore @user annotation, to disable invisible and read-only
	 * @param $can_always_be_null boolean ignore @null annotation and consider this can always be null
	 * @return string
	 */
	public function getEdit(
		Template $template, string $name, bool $ignore_user = false, bool $can_always_be_null = false
	) : string
	{
		if ($name !== '') {
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
			return $this->getEditReflectionProperty($object, $name, $ignore_user, $can_always_be_null);
		}
		if (is_object($object) && isset($property_name) && is_string($property_name)) {
			/** @noinspection PhpUnhandledExceptionInspection object */
			$property = new Reflection_Property($object, $property_name);
			return $this->getEditObjectProperty(
				$object, $property_name, $property, $template, $name, $ignore_user, $can_always_be_null
			);
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
		Default_List_Data $object, Template $template, string $name, bool $ignore_user,
		bool $can_always_be_null
	) : string
	{
		$class_name    = $object->element_class_name;
		$property_name = prev($template->var_names);
		[$property, $property_path, $value] = $this->toEditPropertyExtra($class_name, $property_name);
		$property_edit             = new Html_Builder_Property($property, $value);
		$property_edit->conditions = [];
		$property_edit->name       = $name ?: $property_path;
		$property_edit->pre_path   = '';
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
		object $object, string $property_name, Reflection_Property $property, Template $template,
		string $name, bool $ignore_user, bool $can_always_be_null
	) : string
	{
		$property_value = $property->toReflectionPropertyValue($object, true);
		if ($template->properties_prefix && !$name) {
			$prefix        = $this->getPropertyPrefix($template);
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
		Reflection_Property $property, string $name, bool $ignore_user, bool $can_always_be_null = false
	) : string
	{
		$property_edit             = new Html_Builder_Property($property);
		$property_edit->conditions = [];
		$property_edit->name       = $name ?: $property->path;
		$property_edit->pre_path   = '';
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
	public function getEmpty(Template $template) : bool
	{
		return empty(reset($template->objects));
	}

	//---------------------------------------------------------------------------- getEndWithMultiple
	/**
	 * Multiple properties come last
	 *
	 * @noinspection PhpMixedReturnTypeCanBeReducedInspection If not an array, returns the object
	 * @param $template Template
	 * @return mixed
	 */
	public function getEndWithMultiple(Template $template) : mixed
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
	public function getEscapeName(Template $template) : string
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
	public function getExpand(Template $template) : array
	{
		/** @var $property Reflection_Property */
		$property            = reset($template->objects);
		$expanded_properties = [];
		$expanded_properties = $this->visibleProperties(
			(new Integrated_Properties)->expandUsingProperty(
				$expanded_properties, $property, $template->getParentObject($property->class)
			)
		);
		if ($expanded_properties) {
			/** @var $first_property Reflection_Property_Value PhpStorm should see this, but no */
			$first_property      = reset($expanded_properties);
			$object              = $first_property->getObject(true);
			$expanded_properties = $this->filterProperties($object, $expanded_properties);
		}
		else {
			$expanded_properties = [$property];
		}
		if ($expand_property_path = $template->getParameter(Parameter::EXPAND_PROPERTY_PATH)) {
			foreach ($expanded_properties as $property) {
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
		return $expanded_properties;
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * Returns template's feature method name
	 *
	 * @param $template Template
	 * @return Displayable
	 */
	public function getFeature(Template $template) : Displayable
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
	public function getField(Template $template) : mixed
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
	public function getFile(Template $template) : File
	{
		return new File(reset($template->objects));
	}

	//--------------------------------------------------------------------------- getFilterProperties
	/**
	 * Filter a property or a property list that should not be displayed.
	 * The top object of the template must be a Reflection_Property[], or it will return as null
	 *
	 * @param $template Template
	 * @return Reflection_Property[]|Reflection_Property
	 */
	public function getFilterProperties(Template $template) : array|Reflection_Property
	{
		$properties        = reset($template->objects);
		$properties_filter = $template->getParameter(Parameter::PROPERTIES_FILTER);
		// properties array
		if (is_array($properties)) {
			/** @var $properties Reflection_Property[] */
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
	public function getFirst(Template $template) : mixed
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
	public function getHas(Template $template) : bool
	{
		$object = reset($template->objects);
		return !empty($object);
	}

	//-------------------------------------------------------------------------------- getHasAccessTo
	/**
	 * Check if current user access to the given feature.
	 *
	 * @example 'output'.@hasAccessTo
	 * @example '/ITRocks/Framework/Report/Dashboard/output'.@hasAccessTo
	 * @param $template   Template    The current template object
	 * @param $feature    string|null The feature to check access to
	 * @param $class_name string|null The object class concerned by the feature (optional).
	 *                                By default, the current context class is used.
	 * @return boolean
	 * @todo resolve <!--use ITRocks\Framework\Report--> for 'Report/Dashboard/output' notation too
	 * @todo '\ITRocks\Framework\Report\Dashboard::output'.@hasAccessTo (resolve <!--use too)
	 */
	public function getHasAccessTo(
		Template $template, string $feature = null, string $class_name = null
	) : bool
	{
		$access_control = Access_Control::get(false);
		if (!$access_control) {
			return false;
		}
		reset($template->objects);
		if (!$feature) {
			$feature = current($template->objects);
			if (str_contains($feature, SL)) {
				if (!$class_name) {
					$class_name = Names::pathToClass(ltrim(lLastParse($feature, SL), SL));
				}
				$feature = rLastParse($feature, SL);
			}
			elseif (!$class_name) {
				next($template->objects);
			}
		}
		if (!$class_name) {
			$class_name = $this->displayableClassNameOf(current($template->objects))->value;
		}
		return $access_control->hasAccessTo([$class_name, $feature]);
	}

	//-------------------------------------------------------------------------------------- getImage
	/**
	 * Returns a <img src=""> with a link to the image of the current File property
	 *
	 * @param $template Template
	 * @param $width    integer|null
	 * @param $height   integer|null
	 * @return string
	 */
	public function getImage(Template $template, int $width = null, int $height = null) : string
	{
		return $this->getFile($template)->buildImage($width, $height);
	}

	//------------------------------------------------------------------------------------ getIsFirst
	/**
	 * Returns true if the current array element is the first one
	 *
	 * @param $template Template
	 * @return ?boolean
	 */
	public function getIsFirst(Template $template) : ?bool
	{
		$var_name = null;
		foreach ($template->objects as $array) {
			if (is_array($array)) {
				reset($array);
				return (key($array) === $var_name);
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
	 * @return ?boolean
	 */
	public function getIsLast(Template $template) : ?bool
	{
		$var_name = null;
		foreach ($template->objects as $array) {
			if (is_array($array)) {
				end($array);
				return (key($array) === $var_name);
			}
			$var_name = isset($var_name) ? next($template->var_names) : reset($template->var_names);
		}
		return null;
	}

	//----------------------------------------------------------------------------------- getIsSimple
	/**
	 * Returns true if the property value has a simple display
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getIsSimple(Template $template) : bool
	{
		foreach ($template->objects as $property) {
			if (!($property instanceof Reflection_Property)) {
				continue;
			}
			$is_simple = !$property->getType()->isMultiple();
			if ($is_simple) {
				$widget_annotation = Widget_Annotation::of($property);
				if ($widget_annotation->value) {
					$is_simple = false;
				}
			}
			return $is_simple;
		}
		return true;
	}

	//---------------------------------------------------------------------------------- getIsVisible
	/**
	 * @param $template Template
	 * @return boolean
	 */
	public function getIsVisible(Template $template) : bool
	{
		foreach ($template->objects as $object) {
			if ($object instanceof Reflection_Property) {
				return $this->isPropertyVisible($object);
			}
		}
		return true;
	}

	//--------------------------------------------------------------------------------------- getJson
	/**
	 * Encode using json
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getJson(Template $template) : string
	{
		return json_encode(reset($template->objects));
	}

	//---------------------------------------------------------------------------------------- getKey
	/**
	 * Returns the current key of the current element of the currently read array
	 *
	 * @param $template Template
	 * @return int|string|null
	 */
	public function getKey(Template $template) : int|string|null
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
	public function getLines(Template $template) : array
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
	public function getLink(Template $template, string $feature = '') : string
	{
		foreach ($template->objects as $object) {
			if (is_string($object)) {
				$feature = $object;
			}
			elseif (is_object($object)) {
				return View::link($object, $feature);
			}
		}
		return '';
	}

	//---------------------------------------------------------------------------------------- getLoc
	/**
	 * Returns a value with application of current locales
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $template Template
	 * @return mixed
	 */
	public function getLoc(Template $template) : mixed
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
						/** @noinspection PhpUnhandledExceptionInspection object, property must be valid */
						return Loc::propertyToLocale(
							new Reflection_Property($parent, current($template->var_names)),
							$object
						);
					}
					return Loc::dateToLocale($object);
				}
				else {
					$property_name = reset($template->var_names);
					if (method_exists($object, $property_name)) {
						/** @noinspection PhpUnhandledExceptionInspection method_exists */
						$method = new Reflection_Method($object, $property_name);
						return Loc::methodToLocale($method, reset($template->objects));
					}
					/** @noinspection PhpUnhandledExceptionInspection fatal error is no property exist */
					$property = new Reflection_Property($object, $property_name);
					return Loc::propertyToLocale($property, reset($template->objects));
				}
			}
			elseif (is_array($object)) {
				$value = reset($template->objects);
				if (is_numeric($value)) {
					return str_contains($value, DOT)
						? Loc::floatToLocale($value)
						: Loc::integerToLocale($value);
				}
				return $value;
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
	public function getNull(Template $template) : bool
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
	public function getNumeric(Template $template) : bool
	{
		return is_numeric(reset($template->objects));
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Returns the nearest object from template objects stack
	 *
	 * After this call, current($template->var_names) will give you the var name of the object
	 *
	 * @param $template Template
	 * @return object
	 */
	public function getObject(Template $template) : object
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
	public function getParse(Template $template) : string
	{
		return $template->parseContent(
			str_replace(['&#123;', '&#125;'], ['{', '}'], reset($template->objects))
		);
	}

	//------------------------------------------------------------------------------------- getPrefix
	/**
	 * Prefix the property name with current property prefix string
	 *
	 * @param $template Template
	 * @return string
	 */
	public function getPrefix(Template $template) : string
	{
		$name            = strval(reset($template->objects));
		$property_prefix = $this->getPropertyPrefix($template);
		return $property_prefix ? ($property_prefix . '[' . $name . ']') : $name;
	}

	//-------------------------------------------------------------------------------- getPrintGetter
	/**
	 * @param $template Template
	 * @return string
	 */
	public function getPrintGetter(Template $template) : string
	{
		$value = reset($template->objects);
		$object = next($template->objects);
		if (!is_object($object)) {
			return $value;
		}
		if ($object instanceof Reflection_Property) {
			$property = $object;
			$object   = ($property instanceof Reflection_Property_Value) ? $property->getObject() : null;
			if (!is_object($object)) {
				return $value;
			}
		}
		else {
			try {
				$property = new Reflection_Property($object, reset($template->var_names));
			}
			catch (ReflectionException) {
				return $value;
			}
		}
		/** @var $getter Method_Annotation */
		$getter = $property->getAnnotation('print_getter');
		if (!$getter->value) {
			return $value;
		}
		return $getter->call($object, [end($template->objects)]);
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Returns object's properties, and their display and value
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $template Template
	 * @return Reflection_Property_Value[]
	 */
	public function getProperties(Template $template) : array
	{
		$object             = reset($template->objects);
		$properties_filter  = $template->getParameter(Parameter::PROPERTIES_FILTER);
		$properties_title   = $template->getParameter(Parameter::PROPERTIES_TITLE);
		$properties_tooltip = $template->getParameter(Parameter::PROPERTIES_TOOLTIP);

		/** @noinspection PhpUnhandledExceptionInspection object */
		$class             = new Reflection_Class($object);
		$result_properties = [];

		if ($properties_filter) {
			$properties = [];
			foreach ($properties_filter as $property_path) {
				/** @noinspection PhpUnhandledExceptionInspection $class->name is valid */
				$properties[$property_path] = new Reflection_Property($class->name, $property_path);
			}
		}
		else {
			$properties = $class->getProperties([Reflection_Class::T_SORT, T_EXTENDS, T_USE]);
		}

		foreach ($properties as $property_path => $property) {
			if (!$property->isStatic()) {
				/** @noinspection PhpUnhandledExceptionInspection $property from $object and accessible */
				$property = new Reflection_Property_Value(
					$class->name, $property->path, $object, false, true
				);
				if ($this->isPropertyVisible($property)) {
					if (isset($properties_title) && isset($properties_title[$property_path])) {
						$property->display = $properties_title[$property_path];
					}
					if ($properties_tooltip[$property_path] ?? false) {
						$property->tooltip = $properties_tooltip[$property_path];
					}
					$result_properties[$property_path] = $property;
					if (str_contains($property_path, DOT)) {
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
	 * Stored into a [['content' => Reflection_Property_Value[]]] structure for compatibility with
	 * properties.html
	 *
	 * @param $template Template
	 * @return array [['content' => Reflection_Property_Value[]]]
	 */
	public function getPropertiesOutOfTabs(Template $template) : array
	{
		$properties = [];
		foreach ($this->getProperties($template) as $property_name => $property) {
			if (!Group_Annotation::of($property)->value) {
				$properties[$property_name] = $property;
			}
		}
		return [['content' => $properties]];
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $template Template
	 * @param $name     string|null
	 * @return ?Reflection_Property_Value
	 */
	public function getProperty(Template $template, string $name = null) : ?Reflection_Property_Value
	{
		if ($name) {
			$object = reset($template->objects);
		}
		else {
			reset($template->objects);
			$name   = reset($template->var_names);
			$object = next($template->objects);
		}
		while (isset($template->objects)) {
			if (is_object($object)) {
				/** @noinspection PhpUnhandledExceptionInspection object, property name must exist */
				return new Reflection_Property_Value($object, $name, $object, false, true);
			}
			$object = next($template->objects);
		}
		return null;
	}

	//----------------------------------------------------------------------------- getPropertyBlocks
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @return Block[]
	 */
	protected function getPropertyBlocks(Reflection_Property $property) : array
	{
		$blocks     = [];
		$integrated = Integrated_Annotation::of($property);
		if ($integrated->has(Integrated_Annotation::BLOCK)) {
			$conditions = Conditions_Annotation::of($property);
			$data = $conditions->values()
				? ['conditions' => $conditions->asHtmlAttributeValue(), 'name' => $property->name]
				: [];
			/** @noinspection PhpUnhandledExceptionInspection */
			$blocks[$property->path] = Builder::create(Block::class, [$property->path, $data]);
		}
		foreach ($property->getListAnnotation(Annotation::BLOCK)->values() as $block) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$blocks[$block] = Builder::create(Block::class, [$block]);
		}
		return $blocks;
	}

	//----------------------------------------------------------------------------- getPropertyPrefix
	/**
	 * @param $template Template
	 * @return string
	 */
	public function getPropertyPrefix(Template $template) : string
	{
		$prefix = reset($template->properties_prefix);
		while ($next = next($template->properties_prefix)) {
			$next = (str_contains($next, BS) && class_exists($next))
				? Names::classToDisplay($next)
				: $this->escapeName($next);
			// reverse : name.num => num[name], needed by standard form structure
			if (is_numeric($next)) {
				$next_element = $next;
			}
			else {
				$prefix .= '[' . $next . ']';
				if (isset($next_element)) {
					$prefix .= '[' . $next_element . ']';
					$next_element = null;
				}
			}
		}
		if (isset($next_element)) {
			$prefix .= '[' . $next_element . ']';
		}
		return $prefix;
	}

	//----------------------------------------------------------------------------- getPropertySelect
	/**
	 * @param $template Template
	 * @param $name     string|null
	 * @return string
	 */
	public function getPropertySelect(Template $template, string $name = null) : string
	{
		foreach ($template->objects as $property) {
			if ($property instanceof Reflection_Property) {
				return (new Property_Select($property, $name))->build();
			}
		}
		return '';
	}

	//---------------------------------------------------------------------------- getPropertyTypeCss
	/**
	 * @param $template Template
	 * @return string
	 */
	public function getPropertyTypeCss(Template $template) : string
	{
		foreach ($template->objects as $object) {
			if ($object instanceof Interfaces\Reflection_Property) {
				return Names::classToProperty($object->getType()->getElementTypeAsString());
			}
		}
		return '';
	}

	//---------------------------------------------------------------------------------- getRootClass
	/**
	 * Returns root class from template objects stack
	 *
	 * @param $template Template
	 * @return ?Displayable
	 */
	public function getRootClass(Template $template) : ?Displayable
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
	public function getRootObject(Template $template) : object
	{
		$object = null;
		foreach (array_reverse($template->objects) as $object) {
			if (is_object($object)) {
				break;
			}
		}
		return $object;
	}

	//--------------------------------------------------------------------------------- getSearchEdit
	/**
	 * @param $template Template
	 * @return string
	 */
	public function getSearchEdit(Template $template) : string
	{
		/** @var $reflection_property Reflection_Property */
		$reflection_property = reset($template->objects);
		return $this->getEdit($template, 'search[' . $reflection_property->path . ']', true, true);
	}

	//--------------------------------------------------------------------------------------- getSort
	/**
	 * Returns the sorted version of the objects collection
	 *
	 * @param $template Template
	 * @return object[] the sorted objects collection
	 */
	public function getSort(Template $template) : array
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
	public function getStartingBlocks(Template $template) : array
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
	public function getStoppingBlocks(Template $template) : array
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

	//------------------------------------------------------------------------------------- getString
	/**
	 * @param $template Template
	 * @return string
	 */
	public function getString(Template $template) : string
	{
		return strval(reset($template->objects));
	}

	//----------------------------------------------------------------------------------- getTemplate
	/**
	 * Allow to navigate through the template object
	 *
	 * @example {@template.css}
	 * @param $template Template
	 * @return Template
	 */
	public function getTemplate(Template $template) : Template
	{
		return $template;
	}

	//--------------------------------------------------------------------------------------- getTime
	/**
	 * @param $template Template The first object must be a Date_Time or an ISO date-time
	 * @return string '13:12:20' or an empty string if was not an ISO date-time string
	 */
	public function getTime(Template $template) : string
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
	 * @return mixed
	 */
	public function getTop(Template $template) : mixed
	{
		return $template->getObject();
	}

	//----------------------------------------------------------------------------------------- getTr
	/**
	 * @param $template Template
	 * @return string
	 */
	public function getTr(Template $template) : string
	{
		$value      = array_shift($template->objects);
		$var_name   = array_shift($template->var_names);
		$class_name = strval($this->getClass($template));
		array_unshift($template->objects, $value);
		array_unshift($template->var_names, $var_name);
		return Loc::tr($value, $class_name ?: []);
	}

	//------------------------------------------------------------------------------------ getTypeCss
	/**
	 * @param $template Template
	 * @return string
	 */
	public function getTypeCss(Template $template) : string
	{
		foreach ($template->objects as $object) {
			if ($object instanceof Type) {
				return Names::classToProperty($object->getElementTypeAsString());
			}
		}
		return '';
	}

	//--------------------------------------------------------------------------------------- getUnit
	/**
	 * Get the property unit
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $template Template
	 * @return string
	 */
	public function getUnit(Template $template) : string
	{
		$property = reset($template->objects);
		// find the first next object
		if (!($property instanceof Reflection_Property)) {
			$object        = next($template->objects);
			$property_name = reset($template->var_names);
			while (($object !== false) && !is_object($object)) {
				$object        = next($template->objects);
				$property_name = next($template->var_names);
			}
			if (is_object($object) && isset($property_name) && is_string($property_name)) {
				/** @noinspection PhpUnhandledExceptionInspection object */
				$property = new Reflection_Property($object, $property_name);
			}
		}
		/** @var $unit_annotation Method_Annotation */
		$unit_annotation = $property->getAnnotation('unit');
		/** @noinspection PhpPossiblePolymorphicInvocationInspection Must be valid */
		return $unit_annotation->call($object ?? $property->getObject());
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Returns the current value of the current element of the currently read array
	 *
	 * @param $template Template
	 * @return mixed
	 */
	public function getValue(Template $template) : mixed
	{
		foreach ($template->objects as $key => $array) {
			if (is_array($array) && $key) {
				return $template->objects[$key - 1];
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------------- getValues
	/**
	 * Returns the possible values for a property
	 *
	 * Get from @values or if it is a link to an object, read all objects
	 *
	 * @example {ITRocks\Framework\User.name}
	 * @param $template Template
	 * @return string[]
	 */
	public function getValues(Template $template) : array
	{
		/** @var $property Reflection_Property */
		$property = reset($template->objects);
		$values   = Values_Annotation::of($property)->values();
		if (!$values && $property->getType()->isClass()) {
			$class_name = $property->getType()->getElementTypeAsString();
			$values     = Dao::readAll($class_name, Dao::sort());
		}
		return $values;
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
	public function getVoid(Template $template) : bool
	{
		return strval(reset($template->objects)) === '';
	}

	//--------------------------------------------------------------------------------------- getZero
	/**
	 * Returns true if the object is null or empty string
	 *
	 * @param $template Template
	 * @return boolean
	 */
	public function getZero(Template $template) : bool
	{
		return strval(reset($template->objects)) === '0';
	}

	//----------------------------------------------------------------------------- isPropertyVisible
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isPropertyVisible(Reflection_Property $property) : bool
	{
		return $property->isVisible()
			&& !User_Annotation::of($property)->has(User_Annotation::HIDE_OUTPUT)
			&& !User_Annotation::of($property)->has(User_Annotation::INVISIBLE_OUTPUT);
	}

	//--------------------------------------------------------------------------- toEditPropertyExtra
	/**
	 * Gets property extra data needed for edit widget
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $property   Reflection_Property_Value|Reflection_Property|string
	 * @return array Triplet [Reflection_Property $property, string $property path, mixed $value]
	 */
	protected function toEditPropertyExtra(
		string $class_name, Reflection_Property_Value|Reflection_Property|string $property
	) : array
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
			/** @noinspection PhpUnhandledExceptionInspection class and property must be valid */
			$property = new Reflection_Property($class_name, $property);
		}
		return [$property, $property_path, $value];
	}

	//----------------------------------------------------------------------------- visibleProperties
	/**
	 * @param $properties Reflection_Property[]
	 * @return Reflection_Property[]
	 */
	protected function visibleProperties(array $properties) : array
	{
		foreach ($properties as $property_key => $property) {
			if (!$this->isPropertyVisible($property)) {
				unset($properties[$property_key]);
			}
		}
		return $properties;
	}

}
