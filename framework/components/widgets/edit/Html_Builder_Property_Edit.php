<?php
namespace SAF\Framework;

class Html_Builder_Property_Edit
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $preprop
	/**
	 * @var string
	 */
	private $preprop;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	private $property;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	private $value;

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $property Reflection_Property
	 * @param $value mixed
	 * @param $preprop string
	 */
	public function __construct(Reflection_Property $property = null, $value = null, $preprop = null)
	{
		if (isset($property)) $this->property = $property;
		if (isset($value))    $this->value = $value;
		if (isset($preprop))  $this->preprop = $preprop;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$type = $this->property->getType();
		switch ($type->asString()) {
			case "float":   return $this->buildFloat();
			case "integer": return $this->buildInteger();
			case "string":  return $this->buildString();
		}
		if ($type->isMultiple()) {
			return $this->property->getAnnotation("contained")->value
				? $this->buildCollection()
				: $this->buildMap();
		}
		elseif ($type->isSubClassOf("DateTime")) {
			return $this->buildDateTime();
		}
		elseif ($type->isClass()) {
			return $this->buildObject();
		}
		return $this->value;
	}

	//------------------------------------------------------------------------------- buildCollection
	/**
	 * @return Html_Table
	 */
	private function buildCollection()
	{
		$collection = new Html_Builder_Collection_Edit($this->property, $this->value);
		return $collection->build();
	}

	//--------------------------------------------------------------------------------- buildDateTime
	/**
	 * @return Dom_Element
	 */
	private function buildDateTime()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		$input->setAttribute("autocomplete", "off");
		$input->addClass("datetime");
		return $input;
	}

	//------------------------------------------------------------------------------------ buildFloat
	/**
	 * @return Dom_Element
	 */
	private function buildFloat()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		$input->addClass("float");
		$input->addClass("autowidth");
		return $input;
	}

	//---------------------------------------------------------------------------------- buildInteger
	/**
	 * @return Dom_Element
	 */
	private function buildInteger()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		$input->addClass("integer");
		$input->addClass("autowidth");
		return $input;
	}

	//-------------------------------------------------------------------------------------- buildMap
	/**
	 * @return string
	 */
	private function buildMap()
	{
		return "map";
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * @return string
	 */
	private function buildObject()
	{
		$id_input = new Html_Input(
			$this->getFieldName("id_"), Dao::getObjectIdentifier($this->value)
		);
		$id_input->setAttribute("type", "hidden");
		$id_input->addClass("id");
		$input = new Html_Input(null, strval($this->value));
		$input->setAttribute("autocomplete", "off");
		$input->addClass("combo");
		$input->addClass("autowidth");
		$input->addClass("class:" . Names::classToSet($this->property->getType()->asString()));
		return $id_input . $input;
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @return Dom_Element
	 */
	private function buildString()
	{
		if ($this->property->getAnnotation("multiline")->value) {
			$input = new Html_Textarea($this->getFieldName(), $this->value);
			$input->addClass("autoheight");
		}
		else {
			$input = new Html_Input($this->getFieldName(), $this->value);
		}
		$input->addClass("autowidth");
		return $input;
	}

	//---------------------------------------------------------------------------------- getFieldName
	private function getFieldName($prefix = "")
	{
		$field_name = isset($this->name) ? $this->name : $this->property->name;
		if (!isset($this->preprop)) {
			$field_name = $prefix . $field_name;
		}
		elseif (substr($this->preprop, -2) == "[]") {
			$field_name = substr($this->preprop, 0, -2) . "[" . $prefix . $field_name . "][]";
		}
		else {
			$field_name = $this->preprop . "[" . $prefix . $field_name . "]";
		}
		return $field_name;
	}

}
