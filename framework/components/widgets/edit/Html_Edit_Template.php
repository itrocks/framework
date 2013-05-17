<?php
namespace SAF\Framework;

class Html_Edit_Template extends Html_Template
{

	//-------------------------------------------------------------------------------------- $form_id
	/**
	 * @var string
	 */
	private $form_id;

	public function getFormId()
	{
		return $this->form_id;
	}

	//------------------------------------------------------------------------------- nextFormCounter
	/**
	 * @return integer
	 */
	private function nextFormCounter()
	{
		$counter = isset($_SESSION["Html_Edit_Template"]["form_counter"])
			? $_SESSION["Html_Edit_Template"]["form_counter"] + 1 : 0;
		$_SESSION["Html_Edit_Template"]["form_counter"] = $counter;
		return $counter;
	}

	//-------------------------------------------------------------------------------- parseContainer
	protected function parseContainer($content)
	{
		$i = strpos($content, "<!--BEGIN-->");
		if ($i !== false) {
			$i += 12;
			$j = strrpos($content, "<!--END-->", $i);
			$short_class = Namespaces::shortClassName(get_class($this->object));
			$this->form_id = strtolower($short_class) . "_edit_" . $this->nextFormCounter();
			$action = "/" . $short_class . "/write";
			$content = substr($content, 0, $i)
				. '<form method="POST" name="' . $this->form_id . '" action="' . $action . '">'
				. substr($content, $i, $j - $i)
				. '</form>'
				. substr($content, $j);
		}
		return parent::parseContainer($content);
	}

	//------------------------------------------------------------------------------------ parseValue
	protected function parseValue($objects, $var_name, $as_string = true)
	{
		$property = reset($objects);
		if (($property instanceof Reflection_Property) && ($var_name == "value")) {
			$value = parent::parseValue($objects, $var_name, false);
			$value = (new Html_Builder_Property_Edit($property, $value))->setTemplate($this)->build();
		}
		else {
			$value = parent::parseValue($objects, $var_name, $as_string);
		}
		return $value;
	}

}
