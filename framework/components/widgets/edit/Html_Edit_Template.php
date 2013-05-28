<?php
namespace SAF\Framework;

/**
 * Html template that changes all properties values to form inputs
 */
class Html_Edit_Template extends Html_Template
{

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Some caches ie to know if id where already defined, to increment some counters.
	 *
	 * @var array
	 */
	public $cache = array();

	//-------------------------------------------------------------------------------------- $form_id
	/**
	 * @var string
	 */
	private $form_id;

	//------------------------------------------------------------------------------------- getFormId
	/**
	 * @return string
	 */
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
	/**
	 * Replace code before <!--BEGIN--> and after <!--END--> by the html main container's code
	 *
	 * @param $content string
	 * @return string updated content
	 */
	protected function parseContainer($content)
	{
		$i = strpos($content, "<!--BEGIN-->");
		if ($i !== false) {
			$i += 12;
			$j = strrpos($content, "<!--END-->", $i);
			$short_class = Namespaces::shortClassName(get_class($this->object));
			$short_form_id = strtolower($short_class) . "_edit";
			$this->form_id = $short_form_id . "_" . $this->nextFormCounter();
			$action = "/" . $short_class . "/write";
			$content = substr($content, 0, $i)
				. '<form method="POST"'
				  . ' id=' . $short_form_id . ' name="' . $this->form_id . '" action="' . $action . '">'
				. substr($content, $i, $j - $i)
				. '</form>'
				. substr($content, $j);
		}
		return parent::parseContainer($content);
	}

	//------------------------------------------------------------------------------------ parseValue
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param $objects   mixed[]
	 * @param $var_name  string can be an unique var or path.of.vars
	 * @param $as_string boolean if true, returned value will always be a string
	 * @return string var value after reading value / executing specs (can be an object)
	 */
	protected function parseValue($objects, $var_name, $as_string = true)
	{
		$property = reset($objects);
		if (($property instanceof Reflection_Property) && ($var_name == "value")) {
			$value = parent::parseValue($objects, $var_name, false);
			if (
				($property instanceof Reflection_Property_Value)
				&& ($preprop = lLastParse($property->field(), "[", 1, false))
				&& (
					!isset($this->cache["parsed_id"])
					|| !isset($this->cache["parsed_id"][$this->getFormId()])
					|| !isset($this->cache["parsed_id"][$this->getFormId()][$preprop])
				)
			) {
				$this->cache["parsed_id"][$this->getFormId()][$preprop] = true;
				$parent_object = $property->getObject();
				$id = isset($parent_object) ? Dao::getObjectIdentifier($parent_object) : null;
				$id_value = (new Html_Builder_Type_Edit("id", null, $id, $preprop))->build();
			}
			else {
				$id_value = "";
			}
			$value = $id_value
				. (new Html_Builder_Property_Edit($property, $value))->setTemplate($this)->build();
		}
		else {
			$value = parent::parseValue($objects, $var_name, $as_string);
		}
		return $value;
	}

}
