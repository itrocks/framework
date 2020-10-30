<?php
namespace ITRocks\Framework\Feature\List_Setting;

use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Can_Be_Empty;
use ITRocks\Framework\Tools\String_Class;
use ReflectionException;

/**
 * Data list setting widget for a property (ie a column of the list)
 */
class Property implements Can_Be_Empty
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * @var string
	 */
	public $display;

	//------------------------------------------------------------------------------------- $group_by
	/**
	 * @var boolean
	 */
	public $group_by;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string|null
	 * @param $property_path string|null
	 * @throws ReflectionException
	 */
	public function __construct(string $class_name = null, string $property_path = null)
	{
		if (isset($class_name) && isset($property_path)) {
			$property      = new Reflection_Property_Value($class_name, $property_path);
			$this->display = $property->display();
			$this->path    = $property_path;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->display);
	}

	//----------------------------------------------------------------------------------- htmlGroupBy
	/**
	 * @return string
	 */
	public function htmlGroupBy() : string
	{
		return $this->group_by ? 'checked' : '';
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean
	 */
	public function isEmpty() : bool
	{
		return !(strval($this->display) || strval($this->path));
	}

	//------------------------------------------------------------------------------------ shortTitle
	/**
	 * @return string
	 */
	public function shortTitle() : string
	{
		if (empty($this->display)) {
			$display = str_replace('_', SP, $this->tr($this->path));
		}
		else {
			$display = $this->display;
		}
		return strval((new String_Class($display))->twoLast());
	}

	//----------------------------------------------------------------------------------------- title
	/**
	 * @return string
	 */
	public function title() : string
	{
		return str_replace('_', SP, $this->tr($this->path));
	}

	//-------------------------------------------------------------------------------------------- tr
	/**
	 * Translate
	 *
	 * @param $text string
	 * @return string
	 */
	protected function tr(string $text) : string
	{
		return Locale::current() ? Loc::tr($text) : $text;
	}

}
