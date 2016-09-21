<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Locale\Loc;
use SAF\Framework\Mapper\Empty_Object;
use SAF\Framework\Reflection\Annotation\Property\Link_Annotation;
use SAF\Framework\Reflection\Annotation\Property\Placeholder_Annotation;
use SAF\Framework\Reflection\Annotation\Property\User_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Method_Annotation;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Password;
use SAF\Framework\View\Html\Dom\Element;

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
	 * @param $preprop  string
	 */
	public function __construct(Reflection_Property $property = null, $value = null, $preprop = null)
	{
		if (isset($property)) {
			$this->null     = $property->getAnnotation('null')->value;
			$this->property = $property;
			$this->readonly = $property->getListAnnotation(User_Annotation::ANNOTATION)->has(
				User_Annotation::READONLY
			);
			if (
				!$this->readonly
				&& ($property instanceof Reflection_Property_Value)
				&& ((is_object($value) && Empty_Object::isEmpty($value)) || is_null($value))
			) {
				/** @var $user_default_annotation Method_Annotation */
				$user_default_annotation = $property->getAnnotation('user_default');
				if ($user_default_annotation->value) {
					$value = $user_default_annotation->call($property->getObject());
				}
			}
			$name = $property->pathAsField();
			if (strpos($name, '[')) {
				$preprop2 = lLastParse($name, '[');
				$preprop = $preprop
					? ($preprop . '[' . lParse($preprop2, '[') . '[' . rParse($preprop2, '['))
					: $preprop2;
				$name = lParse(rLastParse($name, '['), ']');
			}
			$this->loadConditions();
			parent::__construct($name, $property->getType(), $value, $preprop);
		}
		else {
			parent::__construct(null, null, $value, $preprop);
		}
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$link = $this->property->getAnnotation('link')->value;
		switch ($link) {
			case Link_Annotation::COLLECTION: return $this->buildCollection();
			case Link_Annotation::MAP:        return $this->buildMap();
			default: return is_array($this->value) && $this->type != 'string[]' ?
				$this->buildMap() :
				$this->buildSingle();
		}
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
		$preprop = (substr($this->preprop, -2) == '[]') ? $this->getFieldName() : $this->preprop;
		$map = new Html_Builder_Map($this->property, $this->value, $preprop);
		$map->setTemplate($this->template);
		return $map->build();
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * @param $filters string[] the key is the name of the filter, the value is the name of the form
	 *   containing its value
	 * @return string
	 */
	public function buildObject($filters = null)
	{
		if (!isset($filters)) {
			$filters_values = $this->property->getListAnnotation('filters')->values();
			if ($filters_values) {
				$properties = $this->property->getDeclaringClass()->getProperties([T_EXTENDS, T_USE]);
				foreach ($filters_values as $filter) {
					if ($properties[$filter]->getType()->isClass()) {
						$filter = 'id_' . $filter;
					}
					$filters[$filter] = $filter;
				}
			}
		}
		return parent::buildObject($filters);
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
	protected function buildString($multiline = false, $values = null)
	{
		$values_captions = [];
		$values = $this->property->getListAnnotation('values')->values();
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
		if ($this->property->getAnnotation('mandatory')->value) {
			$element->setAttribute('required', true);
		}
		if ($this->property->getAnnotation('password')->value) {
			$element->setAttribute('type', 'password');
			$element->setAttribute('value', strlen($this->value) ? Password::UNCHANGED : '');
		}
		if ($placeholder = $this->property->getAnnotation(Placeholder_Annotation::ANNOTATION)->value) {
			$element->setAttribute('placeholder', Loc::tr($placeholder));
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
						list($name, $condition) = explode('=', $condition);
					}
					else {
						$name = $condition;
					}
					$this->conditions[$name] = isset($this->conditions[$name])
						? ($this->conditions[$name] . ',' . $condition)
						: $condition;
				}
			}
		}
	}

}
