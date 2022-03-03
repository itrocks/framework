<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Validate\Property\Mandatory_Annotation;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Reflection\Annotation\Property\Conditions_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Encrypt_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Filters_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Password_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Placeholder_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Tooltip_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Target_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Editor;
use ITRocks\Framework\Tools\Encryption;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Password;
use ITRocks\Framework\View\Html\Dom\Element;
use ITRocks\Framework\View\Html\Dom\Select;

/**
 * Builds a standard form input matching a given property and value
 */
class Html_Builder_Property extends Html_Builder_Type
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

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
		if (!$property) {
			parent::__construct(null, null, $value, $prefix);
			return;
		}
		if ($customized = $property->getAnnotation('customized')->value) {
			$this->classes[] = $customized;
		}
		$this->is_new = ($property instanceof Reflection_Property_Value)
			&& !Dao::getObjectIdentifier($property->getObject());
		$this->null     = $property->getAnnotation('null')->value;
		$this->property = $property;

		$user_annotation = User_Annotation::of($property);

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
		$default_value = $user_default_annotation->value
			? $user_default_annotation->call($this->object)
			: $property->getDefaultValue(true, $this->object);
		if ($default_value || (!is_array($default_value) && strlen($default_value))) {
			$this->data['default-value'] = Loc::propertyToLocale($property, $default_value);
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

		if (
			($property instanceof Reflection_Property_Value) && $property->tooltip && !$this->tooltip
		) {
			$this->tooltip = $property->tooltip;
		}

		// if name contains [...], recalculate name and prefix. TODO explain those rules
		$name = $property->pathAsField();
		if (strpos($name, '[')) {
			$prefix2 = lLastParse($name, '[');
			$prefix  = ($prefix && !is_numeric($prefix))
				? (
					strpos($prefix2, '[')
					? ($prefix . '[' . lParse($prefix2, '[') . '][' . rParse($prefix2, '['))
					: ($prefix . '[' . $prefix2 . ']')
				)
				: $prefix2;
			$name = lParse(rLastParse($name, '['), ']');
		}

		if ($fixed_height = $this->property->getAnnotation('fixed_height')->value) {
			$this->auto_height = false;
			if ($fixed_height !== true) {
				$this->data['height'] = $this->property->getAnnotation('fixed_height')->value;
			}
		}

		if ($fixed_width = $this->property->getAnnotation('fixed_width')->value) {
			$this->auto_width = false;
			if ($fixed_width !== true) {
				$this->data['width'] = $this->property->getAnnotation('fixed_width')->value;
			}
		}

		$this->loadConditions();
		parent::__construct($name, $property->getType(), $value, $prefix);
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
		return ($this->type->isClass() && $this->type->isMultiple() && !$this->type->isMultipleString())
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
		$collection = $this->property->getType()->isAbstractClass()
			? new Html_Builder_Abstract_Collection($this->property, $this->value, $this->pre_path)
			: new Html_Builder_Collection($this->property, $this->value, false, $this->pre_path);
		$collection->template = $this->template;
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
					$property->final_class, $property->name, $this->value, true
				);
			}
			$value = strlen($this->value) ? $this->value : null;
			$this->value = (!$this->null || strlen($this->value)) ? $property->format() : null;
		}
		$element = parent::buildFloat(false);
		if (isset($value)) {
			$this->value = $value;
		}
		return $element;
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
			$filters = Filters_Annotation::of($this->property)->parse($this->object);
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
		foreach ($this->property->getAnnotations('view_data') as $annotation) {
			if (!$annotation->value) {
				break;
			}
			if (strpos($annotation->value, '=')) {
				[$data, $value] = explode('=', $annotation->value);
				$data  = trim($data);
				$value = trim($value);
			}
			else {
				[$data, $value] = [$annotation->value, true];
			}
			$this->data[$data] = $value;
		}
		if (
			!$this->property->getType()->isMultiple()
			&& ($user_changes = $this->property->getAnnotations('user_change'))
		) {
			/** @var $user_changes Method_Target_Annotation[] */
			foreach ($user_changes as $user_change) {
				$object = ($this->property instanceof Reflection_Property_Value)
					? $this->property->getObject()
					: $this->object;
				if ($object && $user_change->is_composite) {
					/** @var $object Component */
					$object = $object->getComposite();
					if ($object && strpos($user_change, '::')) {
						$user_change->value = Builder::current()->sourceClassName(get_class($object))
							. '::' . rParse($user_change->value, '::');
					}
				}
				$this->on_change[] = $user_change->asHtmlData($object);
			}
			if ($this->on_change) {
				$this->realtime_change = $this->property->getAnnotation('user_change_realtime')->value;
			}
		}
		if (!$this->property->getAnnotation('empty_check')->value) {
			$this->data['no-empty-check'] = true;
		}
		if ($placeholder = Placeholder_Annotation::of($this->property)->callProperty($this->property)) {
			$this->attributes['placeholder'] = $placeholder;
		}
		$this->required = Mandatory_Annotation::of($this->property)->value;
		if (!isset($this->tooltip)) {
			$this->tooltip = Tooltip_Annotation::of($this->property)->callProperty($this->property);
		}
		if ($data_entries = $this->property->getListAnnotation('data')->value) {
			foreach ($data_entries as $data) {
				if (!strpos($data, '=')) {
					$data .= '=' . $data;
				}
				$this->data[lParse($data, '=')] = rParse($data, '=');
			}
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
		if (
			($element instanceof Select)
			&& isset($element->values[''])
			&& !$this->property->getAnnotation('user_empty_value')->value
		) {
			unset($element->values['']);
		}
		if ($this->property->getAnnotation('editor')->value) {
			// @TODO Low : When declaring a editor, it would have to be a default multiline
			$editor_name = $this->property->getAnnotation('editor')->value;
			$element->addClass(Editor::buildClassName($editor_name));
		}
		if (
			Encrypt_Annotation::of($this->property)->value
			|| Password_Annotation::of($this->property)->value
		) {
			if (Encrypt_Annotation::of($this->property)->value === Encryption::SENSITIVE_DATA) {
				if ($value = (new Sensitive_Data)->decrypt($this->value, $this->property)) {
					$element->setAttribute('value', $value);
					return $element;
				}
				$element->setData('sensitive');
			}
			if (strlen($this->value) || Password_Annotation::of($this->property)->value) {
				$element->setAttribute('type', 'password');
			}
			$element->setAttribute('value', strlen($this->value) ? Password::UNCHANGED : '');
		}
		if (
			!$values
			&& !is_null($translate_mode = $this->property->getAnnotation('translate')->value)
		) {
			if (empty($translate_mode)) {
				$translate_mode = 'data';
			}
			$element->setData('translate', $translate_mode);
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
