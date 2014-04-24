<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Reflection\Annotation\Property\Link_Annotation;
use SAF\Framework\Reflection\Annotation\Property\User_Annotation;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Password;
use SAF\Framework\View\Html\Dom\Element;
use SAF\Framework\View\Html\Dom\Table;

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

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @param $preprop  string
	 */
	public function __construct(Reflection_Property $property = null, $value = null, $preprop = null)
	{
		if (isset($property)) {
			$name = $property->pathAsField();
			if (strpos($name, '[')) {
				$preprop2 = lLastParse($name, '[');
				$preprop = $preprop
					? ($preprop . '[' . lParse($preprop2, '[') . '[' . rParse($preprop2, '['))
					: $preprop2;
				$name = lParse(rLastParse($name, '['), ']');
			}
			parent::__construct($name, $property->getType(), $value, $preprop);
			$this->null = $property->getAnnotation('null')->value;
			$this->property = $property;
			$this->readonly = $property->getListAnnotation('user')->has(User_Annotation::READONLY);
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
			default:           return parent::build();
		}
	}

	//------------------------------------------------------------------------------- buildCollection
	/**
	 * @return Table
	 */
	private function buildCollection()
	{
		if (!isset($this->template)) {
			$this->template = new Html_Template();
		}
		if (!$this->value) {
			$this->value = [];
		}
		$collection = new Html_Builder_Collection($this->property, $this->value);
		$collection->setTemplate($this->template);
		return $collection->build();
	}

	//-------------------------------------------------------------------------------------- buildMap
	/**
	 * @return string
	 */
	private function buildMap()
	{
		if (!$this->value) {
			$this->value = [];
		}
		$map = new Html_Builder_Map($this->property, $this->value);
		return $map->build();
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * @param $conditions string[] the key is the name of the condition, the value is the name of the
	 *   value that enables the condition
	 * @param $filters string[] the key is the name of the filter, the value is the name of the form
	 *   containing its value
	 * @return string
	 */
	public function buildObject($conditions = null, $filters = null)
	{
		if (!isset($conditions)) {
			$conditions_values = $this->property->getListAnnotation('conditions')->values();
			if ($conditions_values) {
				foreach ($conditions_values as $condition) {
					if (strpos($condition, '=')) {
						list($name, $condition) = explode('=', $condition);
					}
					else {
						$name = $condition;
					}
					$conditions[$name] = isset($conditions[$name])
						? ($conditions[$name] . ',' . $condition)
						: $condition;
				}
			}
		}
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
		return parent::buildObject($conditions, $filters);
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
		return $element;
	}

}
