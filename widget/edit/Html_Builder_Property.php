<?php
namespace ITRocks\Framework\Widget\Edit;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Reflection\Annotation\Property\Conditions_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Placeholder_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Change_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Editor;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\View\Html\Dom\Element;
use ITRocks\Framework\Widget\Validate\Property\Mandatory_Annotation;

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
			if ($customized = $property->getAnnotation('customized')->value) {
				$this->classes[] = $customized;
			}
			$this->is_new = ($property instanceof Reflection_Property_Value)
				&& !Dao::getObjectIdentifier($property->getObject());
			$this->null     = $property->getAnnotation('null')->value;
			$this->property = $property;

			/** @var $user_annotation User_Annotation */
			$user_annotation = $property->getListAnnotation(User_Annotation::ANNOTATION);

			/** @var $user_default_annotation Method_Annotation */
			$user_default_annotation = $property->getAnnotation('user_default');

			if ($property instanceof Reflection_Property_Value) {
				if (!isset($value)) {
					$value = $property->value();
				}
				// if value is empty, then get @user_default ?: @default value (by SM)
				if (is_null($value) || (is_object($value) && Empty_Object::isEmpty($value))) {
					$object = $property->getObject(true);
					if (!Dao::getObjectIdentifier($object)) {
						$value = $user_default_annotation->value
							? $user_default_annotation->call($object)
							: $property->getDefaultValue(true, $object);
					}
				}
			}

			// 1st, get read_only from @user readonly
			$this->readonly = ($user_annotation->has(User_Annotation::READONLY)
				// Create_only annotation and object already exists ? ==> readonly = true
				|| (
					$user_annotation->has(User_Annotation::CREATE_ONLY)
					// TODO Are they the best conditions to test ?
					&& ($property instanceof Reflection_Property_Value)
					&& is_object($property->getObject())
					&& !Empty_Object::isEmpty($property->getObject())
				)
			);

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

			if (
				($property instanceof Reflection_Property_Value) && $property->tooltip && !$this->tooltip
			) {
				$this->tooltip = $property->tooltip;
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
		return (is_array($this->value) && !$this->type->isMultipleString())
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $format boolean
	 * @return Element
	 */
	protected function buildFloat($format = true)
	{
		if ($format) {
			$property = $this->property;
			if (!($property instanceof Reflection_Property_Value)) {
				/** @noinspection PhpUnhandledExceptionInspection valid $property */
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
	 * @noinspection PhpDocMissingThrowsInspection
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
					/** @noinspection PhpUnhandledExceptionInspection $filter property name must be valid */
					if ((new Reflection_Property($foreign_class_name, $filter))->getType()->isClass()) {
						$filter = 'id_' . $filter;
					}
					if (
						is_numeric($filter_value_name)
						|| (
							in_array(substr($filter_value_name, 0, 1), [DQ, Q])
							&& (substr($filter_value_name, 0, 1) === substr($filter_value_name, -1))
						)
					) {
						$filters[$filter] = $filter_value_name;
					}
					else {
						/** @noinspection PhpUnhandledExceptionInspection $filter value name must be valid */
						$property         = new Reflection_Property($class_name, $filter_value_name);
						$filters[$filter] = $property->pathAsField(true);
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
			/** @var $user_changes User_Change_Annotation[] */
			foreach ($user_changes as $user_change) {
				$this->on_change[] = str_replace([BS, '::'], SL, $user_change->value)
					. ($user_change->target ? (SP . $user_change->target) : '');
			}
		}
		if (!$this->property->getAnnotation('empty_check')->value) {
			$this->data['no-empty-check'] = true;
		}
		if ($placeholder = Placeholder_Annotation::of($this->property)->callProperty($this->property)) {
			$this->attributes['placeholder'] = $placeholder;
		}
		$this->required    = Mandatory_Annotation::of($this->property)->value;
		if (!isset($this->tooltip)) {
			$this->tooltip = Tooltip_Annotation::of($this->property)->callProperty($this->property);
		}
		return parent::build();
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @param $multiline      boolean keep this value empty, it is not used
	 *                        because the @multiline annotation is automatically used
	 * @param $values         string[] keep this value empty, it is not used
	 *                        because the @values annotation is automatically used
	 * @param $ordered_values boolean keep this value default, it is not used
	 *                        because the @ordered_values is automatically used when @values is set
	 * @return Element
	 */
	protected function buildString($multiline = false, array $values = null, $ordered_values = false)
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
		if ($values_captions) {
			$ordered_values = $this->property->getAnnotation('ordered_values')->value;
		}
		$element = parent::buildString(
			$this->property->getAnnotation('multiline')->value,
			$values_captions,
			$ordered_values
		);
		if ($this->property->getAnnotation('editor')->value) {
			// @TODO Low : When declaring a editor, it would have to be a default multiline
			$version_editor = $this->property->getAnnotation('editor')->value;
			$element->addClass(Editor::buildClassName($version_editor));
		}
		if ($this->property->getAnnotation('password')->value) {
			$element->setAttribute('type', 'password');
			$element->setAttribute('value', strlen($this->value) ? Password::UNCHANGED : '');
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
			$this->conditions = Conditions_Annotation::of($this->property)->values();
		}
	}

}
