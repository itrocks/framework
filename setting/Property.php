<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Tools\Can_Be_Empty;
use ITRocks\Framework\Tools\String_Class;
use ReflectionException;

/**
 * Common setting property things
 */
abstract class Property implements Can_Be_Empty
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * Display must be stored already translated
	 *
	 * @var string
	 */
	public string $display;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public string $path;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property_Value 
	 */
	protected Reflection_Property_Value $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string|null
	 * @param $property_path string|null
	 * @throws ReflectionException
	 */
	public function __construct(string $class_name = null, string $property_path = null)
	{
		if (!isset($class_name) || !isset($property_path)) {
			return;
		}
		$this->property = new Reflection_Property_Value($class_name, $property_path);
		$this->display  = $this->property->display();
		$this->path     = $property_path;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->display;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean
	 */
	public function isEmpty() : bool
	{
		return !($this->display || $this->path);
	}

	//------------------------------------------------------------------------------------ shortTitle
	/**
	 * @return string
	 */
	public function shortTitle() : string
	{
		$display = $this->display ?: str_replace('_', SP, $this->tr($this->path));
		return (new String_Class($display))->twoLast();
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
