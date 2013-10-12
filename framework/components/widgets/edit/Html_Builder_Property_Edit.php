<?php
namespace SAF\Framework;

/**
 * Builds a standard form input matching a given property and value
 */
class Html_Builder_Property_Edit extends Html_Builder_Type_Edit
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
			$name = ($property instanceof Reflection_Property_Value)
				? $property->field() : $property->name;
			if (strpos($name, "[")) {
				$preprop2 = lLastParse($name, "[");
				$preprop = $preprop
					? ($preprop . "[" . lParse($preprop2, "[") . "[" . rParse($preprop2, "["))
					: $preprop2;
				$name = lParse(rLastParse($name, "["), "]");
			}
			parent::__construct($name, $property->getType(), $value, $preprop);
			$this->property = $property;
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
		$link = $this->property->getAnnotation("link")->value;
		switch ($link) {
			case "Collection": return $this->buildCollection();
			case "Map":        return $this->buildMap();
			default:           return parent::build();
		}
	}

	//------------------------------------------------------------------------------- buildCollection
	/**
	 * @return Html_Table
	 */
	private function buildCollection()
	{
		if (!isset($this->template)) {
			$this->template = new Html_Edit_Template();
		}
		if (!$this->value) {
			$this->value = array();
		}
		$collection = new Html_Builder_Collection_Edit($this->property, $this->value);
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
			$this->value = array();
		}
		$map = new Html_Builder_Map_Edit($this->property, $this->value);
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
	protected function buildObject($conditions = null, $filters = null)
	{
		if (!isset($conditions)) {
			$conditions_values = $this->property->getListAnnotation("conditions")->values();
			if ($conditions_values) {
				foreach ($conditions_values as $condition) {
					if (strpos($condition, "=")) {
						list($name, $condition) = explode("=", $condition);
					}
					else {
						$name = $condition;
					}
					$conditions[$name] = isset($conditions[$name])
						? ($conditions[$name] . "," . $condition)
						: $condition;
				}
			}
		}
		if (!isset($filters)) {
			$filters_values = $this->property->getListAnnotation("filters")->values();
			if ($filters_values) {
				$properties = $this->property->getDeclaringClass()->getAllProperties();
				foreach ($filters_values as $filter) {
					if ($properties[$filter]->getType()->isClass()) {
						$filter = "id_" . $filter;
					}
					$filters[$filter] = $filter;
				}
			}
		}
		return parent::buildObject($conditions, $filters);
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @param $multiline boolean keep this value empty, it is not used as the @multiline annotation is automatically used
	 * @param $values    string[] keep this value empty, it is not used as the @values annotation is automatically used
	 * @return Dom_Element
	 */
	protected function buildString($multiline = false, $values = null)
	{
		$values_captions = array();
		$values = $this->property->getListAnnotation("values")->values();
		foreach ($values as $value) {
			$values_captions[$value] = Names::propertyToDisplay($value);
		}
		return parent::buildString(
			$this->property->getAnnotation("multiline")->value,
			$values_captions
		);
	}

}
