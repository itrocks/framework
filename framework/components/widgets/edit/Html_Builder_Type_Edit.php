<?php
namespace SAF\Framework;

class Html_Builder_Type_Edit
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
	protected $preprop;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var Type
	 */
	protected $type;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	protected $value;

	//----------------------------------------------------------------------------------------- build
	/**
	 * @param $name    string
	 * @param $type    Type
	 * @param $value   mixed
	 * @param $preprop string
	 */
	public function __construct($name = null, Type $type = null, $value = null, $preprop = null)
	{
		if (isset($name))    $this->name = $name;
		if (isset($type))    $this->type = $type;
		if (isset($value))   $this->value = $value;
		if (isset($preprop)) $this->preprop = $preprop;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$type = $this->type;
		switch ($type->asString()) {
			case "float":    return $this->buildFloat();
			case "integer":  return $this->buildInteger();
			case "string":   return $this->buildString();
			case "string[]": return "string[]";
		}
		if ($type->isInstanceOf("DateTime")) {
			return $this->buildDateTime();
		}
		elseif ($type->isClass()) {
			return $this->buildObject();
		}
		return $this->value;
	}

	//--------------------------------------------------------------------------------- buildDateTime
	/**
	 * @return Dom_Element
	 */
	protected function buildDateTime()
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
	protected function buildFloat()
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
	protected function buildInteger()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		$input->addClass("integer");
		$input->addClass("autowidth");
		return $input;
	}

	//----------------------------------------------------------------------------------- buildObject
	/**
	 * @return string
	 */
	protected function buildObject()
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
		$input->addClass(
			"class:" . Namespaces::shortClassName(Names::classToSet($this->type->asString()))
		);
		return $id_input . $input;
	}

	//----------------------------------------------------------------------------------- buildString
	/**
	 * @param $multiline boolean
	 * @return Dom_Element
	 */
	protected function buildString($multiline = false)
	{
		if ($multiline) {
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
	/**
	 * @param string $prefix
	 * @return string
	 */
	protected function getFieldName($prefix = "")
	{
		$field_name = $this->name;
		if (empty($field_name) && $this->preprop) {
			$prefix = "";
		}
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
