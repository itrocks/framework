<?php
namespace SAF\Framework;

/**
 * Builds a standard form input matching a given data type and value
 */
class Html_Builder_Type_Edit
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @link Object
	 * @var Html_Edit_Template
	 */
	public $template;

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
		if (!isset($type)) {
			return $this->buildId();
		}
		else {
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

	//--------------------------------------------------------------------------------------- buildId
	/**
	 * @return Dom_Element
	 */
	protected function buildId()
	{
		$input = new Html_Input($this->getFieldName(), $this->value);
		$input->setAttribute("type", "hidden");
		$input->addClass("id");
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
		$class_name = $this->type->asString();
		// id input
		$id_input = new Html_Input(
			$this->getFieldName("id_"), Dao::getObjectIdentifier($this->value)
		);
		$id_input->setAttribute("type", "hidden");
		$id_input->addClass("id");
		// visible input
		$input = new Html_Input(null, strval($this->value));
		$input->setAttribute("autocomplete", "off");
		$input->addClass("combo");
		$input->addClass("autowidth");
		$input->addClass(
			"class:" . Namespaces::shortClassName(Names::classToSet($class_name))
		);
		// "add" anchor
		$add = new Html_Anchor(
			View::current()->link(get_class($this->value), "new")
			. (isset($this->template)
				? ("?fill_combo=" . $this->template->getFormId() . "." . $this->getFieldName("id_", false))
				: ""
			),
			"add"
		);
		$add->addClass("add");
		$add->addClass("action");
		$add->setAttribute("target", "#_blank");
		$add->setAttribute("title",
			"|Edit ¦" . strtolower(Namespaces::shortClassName($class_name)) . "¦|"
		);
		// "more" button
		$more = new Html_Button("more");
		$more->addClass("more");
		$more->addClass("action");
		$more->setAttribute("tabindex", -1);

		return $id_input . $input . $more . $add;
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
			$input->setAttribute("autocomplete", "off");
		}
		$input->addClass("autowidth");
		return $input;
	}

	//---------------------------------------------------------------------------------- getFieldName
	/**
	 * @param $prefix            string
	 * @param $counter_increment boolean
	 * @return string
	 */
	public function getFieldName($prefix = "", $counter_increment = true)
	{
		$field_name = $this->name;
		if (empty($field_name) && $this->preprop) {
			$prefix = "";
		}
		if (!isset($this->preprop)) {
			$field_name = $prefix . $field_name;
		}
		elseif (substr($this->preprop, -2) == "[]") {
			$field_name = substr($this->preprop, 0, -2) . "[" . $prefix . $field_name . "]";
			$count = $this->nextCounter($field_name, $counter_increment);
			$field_name .= "[$count]";
		}
		else {
			$field_name = $this->preprop . "[" . $prefix . $field_name . "]";
		}
		return $field_name;
	}

	//----------------------------------------------------------------------------------- nextCounter
	/**
	 * Returns next counter for field name into current form context
	 *
	 * @param $field_name string
	 * @param $increment  boolean
	 * @return integer
	 */
	public function nextCounter($field_name, $increment = true)
	{
		$form = $this->template->getFormId();
		$counter = isset($this->template->cache["counter"])
			? $this->template->cache["counter"] : array();
		if (!isset($counter[$form])) {
			$counter[$form] = array();
		}
		$count = isset($counter[$form][$field_name]) ? $counter[$form][$field_name] + $increment : 0;
		$counter[$form][$field_name] = $count;
		$this->template->cache["counter"] = $counter;
		return $count;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Html_Edit_Template
	 * @return Html_Builder_Type_Edit
	 */
	public function setTemplate(Html_Edit_Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
