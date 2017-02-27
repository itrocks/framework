<?php
namespace ITRocks\Framework\Widget\Edit;

use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Placeholder_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Editor;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\View\Html\Dom\Element;

/**
 * Builds a standard form input matching a given property and value
 */
class Html_Builder_Property extends Html_Builder_Type
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	protected $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @param $prefix   string prefix to property name
	 */
	public function __construct(Reflection_Property $property = null, $value = null, $prefix = null)
	{
		if (isset($property)) {
			$this->null     = $property->getAnnotation('null')->value;
			$this->property = $property;

			/** @var $user_annotation User_Annotation */
			$user_annotation = $property->getListAnnotation(User_Annotation::ANNOTATION);
			/** @var $user_default_annotation Method_Annotation */
			$user_default_annotation = $property->getAnnotation('user_default');

			// get default value
			if (($property instanceof Reflection_Property_Value)
				&& ((is_object($value) && Empty_Object::isEmpty($value)) || is_null($value))
				&& $user_default_annotation->value
			) {
				$value = $user_default_annotation->call($property->getObject());
			}

			// 1st, get read_only from @user readonly
			$this->readonly = $user_annotation->has(User_Annotation::READONLY);
			if (
				!$this->readonly
				&& (is_null($value) || (is_object($value) && Empty_Object::isEmpty($value)))
			) {
				// if there is @user_default, there can not be @user if_empty
				if ($user_default_annotation->value && $user_annotation->has(User_Annotation::IF_EMPTY)) {
					$flag_cannot_be_if_empty = true;
				}
			}

			// 2nd, if not read_only but has a value and @user if_empty, then set read_only
			if (
				!$this->readonly
				&& ((is_object($value) && !Empty_Object::isEmpty($value)) || !empty($value))
				&& (!isset($flag_cannot_be_if_empty) || !$flag_cannot_be_if_empty)
			) {
				$this->readonly = $user_annotation->has(User_Annotation::IF_EMPTY);
			}

			// if name contains [...], recalculate name and prefix. TODO explain those rules
			$name = $property->pathAsField();
			if (strpos($name, '[')) {
				$prefix2 = lLastParse($name, '[');
				$prefix  = $prefix
					? ($prefix . '[' . lParse($prefix2, '[') . '[' . rParse($prefix2, '['))
					: $prefix2;
				$name = lParse(rLastParse($name, '['), ']');
			}

			$this->loadConditions();
			parent::__construct($name, $property->getType(), $value, $prefix);
		}
		else {
			parent::__construct(null, null, $value, $prefix);
		}
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		switch (Link_Annotation::of($this->property)->value) {
			case Link_Annotation::COLLECTION: return $this->buildCollection();
			case Link_Annotation::MAP:        return $this->buildMap();
		}
		return is_array($this->value) && ($this->type !== 'string[]')
			? $this->buildMap()
			: $this->buildSingle();
	}

	//------------------------------------------------------------------------------- buildCollection
	/**
	 * @return string
	 */
	private function buildCollection()
	{
		if (!isset($this->template)) {
			$this->template = new Html_Template();
		}
		if (!$this->value) {
			$this->value = [];
		}
		$collection = $this->property->getType()->asReflectionClass()->isAbstract()
			? new Html_Builder_Abstract_Collection($this->property, $this->value)
			: new Html_Builder_Collection($this->property, $this->value);
		$collection->preprop = $this->preprop;
		$collection->setTemplate($this->template);
		return $collection->build();
	}

	//------------------------------------------------------------------------------------ buildFloat
	/**
	 * @param $format boolean
	 * @return Element
	 */
	protected function buildFloat($format = true)
	{
		if ($format) {
			$property = $this->property;
			if (!($property instanceof Reflection_Property_Value)) {
				$property = new Reflection_Property_Value(
					$property->class, $property->name, $this->value, true
				);
			}
			$value = strlen($this->value) ? $this->value : null;
			$this->value = (!$this->null || strlen($this->value)) ? $property->format() : null;
		}
		$result = parent::buildFloat(false);
		if (isset($value)) {
			$this->value = $value;
		}
		return $result;
	}

	//-------------------------------------------------------------------------------------- buildMap
	/**
	 * @return string
	 */
	private function buildMap()
	{
		if (!isset($this->template)) {
			$this->template = new Html_Template();
		}
		if (!$this->value) {
			$this->value = [];
		}
		$map = new Html_Builder_Map($this->property, $this->value, $this->getFieldName());
		$map->setTemplate($this->template);
		return $map->build();
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * @param $filters string[] the key is the name of the filter, the value is the name of the form
	 *   containing its value
	 * @param $as_string boolean true if the object should be used as a string
	 * @return string
	 */
	public function buildObject(array $filters = null, $as_string = null)
	{
		if (!isset($filters)) {
			$filters_annotation = $this->property->getListAnnotation('filters');
			$filters_values     = $filters_annotation->values();
			if ($filters_values) {
				$class_name         = $this->property->getFinalClassName();
				$foreign_class_name = $this->property->getType()->getElementTypeAsString();
				foreach ($filters_values as $filter) {
					if (strpos($filter, '=')) {
						list($filter, $filter_value_name) = explode('=', $filter);
						$filter            = trim($filter);
						$filter_value_name = trim($filter_value_name);
					}
					else {
						$filter_value_name = $filter;
					}
					if ((new Reflection_Property($foreign_class_name, $filter))->getType()->isClass()) {
						$filter = 'id_' . $filter;
					}
					if ((new Reflection_Class($class_name))->hasProperty($filter_value_name)) {
						$property         = (new Reflection_Property($class_name, $filter_value_name));
						$filters[$filter] = $property->pathAsField(true);
					}
					else {
						$filters[$filter] = '#' . $filter_value_name;
					}
				}
			}
		}
		$as_string = isset($as_string)
			? $as_string
			: (
				$this->property->getAnnotation(Store_Annotation::ANNOTATION)->value
				=== Store_Annotation::STRING
			);
		return parent::buildObject($filters, $as_string);
	}

	//----------------------------------------------------------------------------------- buildSingle
	/**
	 * @return string
	 */
	protected function buildSingle()
	{
		if (
			!$this->property->getType()->isMultiple()
			&& ($user_changes = $this->property->getAnnotations('user_change'))
		) {
			foreach ($user_changes as $user_change) {
				$this->on_change[] = str_replace([BS, '::'], SL, $user_change->value);
			}
		}
		$this->placeholder = Placeholder_Annotation::of($this->property)->callProperty($this->property);
		$this->tooltip     = Tooltip_Annotation    ::of($this->property)->callProperty($this->property);
		return parent::build();
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @param $multiline boolean keep this value empty, it is not used because the @multiline
	 *        annotation is automatically used
	 * @param $values    string[] keep this value empty, it is not used because the @values annotation
	 *        is automatically used
	 * @return Element
	 */
	protected function buildString($multiline = false, array $values = null)
	{
		$values_captions = [];
		$values          = $this->property->getListAnnotation('values')->values();
		foreach ($values as $value) {
			$values_captions[$value] = Names::propertyToDisplay($value);
		}
		// @deprecated 97759a48fc96b5efccc0f12f8b12539fb8cb4a0b : this could not happen anymore
		if (
			$values_captions
			&& !$this->type->isMultipleString()
			&& !in_array($this->value, $values_captions)
		) {
			$values_captions[$this->value] = $this->value;
		}
		$element = parent::buildString(
			$this->property->getAnnotation('multiline')->value,
			$values_captions
		);
		if ($this->property->getAnnotation('editor')->value) {
			// @TODO Low : When declaring a editor, it would have to be a default mulitline
			$version_editor = $this->property->getAnnotation('editor')->value;
			$element->addClass(Editor::buildClassName($version_editor));
		}
		if ($this->property->getAnnotation('mandatory')->value) {
			$element->setAttribute('required', true);
		}
		if ($this->property->getAnnotation('password')->value) {
			$element->setAttribute('type', 'password');
			$element->setAttribute('value', strlen($this->value) ? Password::UNCHANGED : '');
		}
		if ($placeholder = Placeholder_Annotation::of($this->property)->callProperty($this->property)) {
			$element->setAttribute('placeholder', $placeholder);
		}
		return $element;
	}

	//-------------------------------------------------------------------------------- loadConditions
	/**
	 * Load conditions with annotation and stock in attribute
	 */
	private function loadConditions()
	{
		if (!isset($this->conditions)) {
			$conditions_values = $this->property->getListAnnotation('conditions')->values();
			$this->conditions  = [];
			if ($conditions_values) {
				foreach ($conditions_values as $condition) {
					if (strpos($condition, '=')) {
						list($property_name, $condition) = explode('=', $condition);
					}
					else {
						$property_name = $condition;
					}
					if (
						in_array($condition, [_FALSE, _TRUE])
						&& $this->property->getFinalClass()->getProperty($property_name)->getType()->isBoolean()
					) {
						$condition = ($condition === _TRUE) ? 1 : 0;
					}
					$this->conditions[$property_name] = isset($this->conditions[$property_name])
						? ($this->conditions[$property_name] . ',' . $condition)
						: $condition;
				}
			}
		}
	}

}
