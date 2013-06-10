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
		$collection = new Html_Builder_Collection_Edit($this->property, $this->value);
		return $collection->setTemplate($this->template)->build();
	}

	//-------------------------------------------------------------------------------------- buildMap
	/**
	 * @return string
	 */
	private function buildMap()
	{
		$map = new Html_Builder_Map_Edit($this->property, $this->value);
		return $map->build();
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @param $multiline boolean keep this value empty, it is not used as the @multiline annotation is taken instead
	 * @return Dom_Element
	 */
	protected function buildString($multiline = false)
	{
		return parent::buildString($this->property->getAnnotation("multiline")->value);
	}

}
