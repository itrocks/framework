<?php
namespace SAF\Framework;

class Html_Builder_Property
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public $value;

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param Reflection_Property $property
	 * @param mixed $value
	 */
	public function __construct(Reflection_Property $property = null, $value = null)
	{
		if (isset($property)) $this->property = $property;
		if (isset($value))    $this->value = $value;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$type = $this->property->getType();
		switch ($type) {
			case "float":   return $this->buildFloat();
			case "integer": return $this->buildInteger();
			case "string":  return $this->buildString();
		}
		if (Type::isMultiple($type)) {
			if ($this->property->getAnnotation("contained")->value) {
				return $this->buildCollection();
			}
			else {
				return $this->buildMap();
			}
		}
		$type = Namespaces::fullClassName($type);
		if (is_subclass_of($type, "DateTime")) {
			return $this->buildDateTime();
		}
		if (class_exists($type)) {
			return $this->buildObject();
		}
		return $this->value;
	}

	//------------------------------------------------------------------------------- buildCollection
	private function buildCollection()
	{
		return "collection et on peut mettre un paquet de baratin regardes ça dépassera pas";
	}

	//--------------------------------------------------------------------------------- buildDateTime
	/**
	 * @return Dom_Element
	 */
	private function buildDateTime()
	{
		$input = new Html_Input($this->property->name, $this->value);
		$input->addClass("datetime");
		return $input;
	}

	//------------------------------------------------------------------------------------ buildFloat
	/**
	 * @return Dom_Element
	 */
	private function buildFloat()
	{
		$input = new Html_Input($this->property->name, $this->value);
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
		$input = new Html_Input($this->property->name, $this->value);
		$input->addClass("integer");
		$input->addClass("autowidth");
		return $input;
	}

	//-------------------------------------------------------------------------------------- buildMap
	private function buildMap()
	{
		return "map";
	}

	//----------------------------------------------------------------------------------- buildObject
	private function buildObject()
	{
		$id_input = new Html_Input(
			"id_" . $this->property->name, Dao::getObjectIdentifier($this->value)
		);
		$id_input->setAttribute("type", "hidden");
		$id_input->addClass("id");
		$input = new Html_Input(null, $this->value);
		$input->setAttribute("autocomplete", "off");
		$input->addClass("combo");
		$input->addClass("autowidth");
		$input->addClass("class:" . Names::classToSet($this->property->getType()));
		return $id_input . $input;
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @return Dom_Element
	 */
	private function buildString()
	{
		if ($this->property->getAnnotation("multiline")->value) {
			$input = new Html_Textarea($this->property->name, $this->value);
			$input->addClass("autoheight");
		}
		else {
			$input = new Html_Input($this->property->name, $this->value);
		}
		$input->addClass("autowidth");
		return $input;
	}

}
