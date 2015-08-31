<?php
namespace SAF\Framework\Widget\Output_Setting;

use SAF\Framework\Locale\Loc;
use SAF\Framework\Reflection\Reflection_Property_Value;

/**
 * Output setting widget property
 */
class Property
{

	//-------------------------------------------------------------------------------------- $display
	/**
	 * @var string
	 */
	public $display;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public $path;

	//------------------------------------------------------------------------------------ $read_only
	/**
	 * @var boolean
	 */
	public $read_only;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property  Reflection_Property_Value|null
	 * @param $read_only boolean
	 */
	public function __construct(Reflection_Property_Value $property = null, $read_only = null)
	{
		if (isset($property)) {
			$this->display = Loc::tr($property->display());
			$this->path    = $property->path;
		}
		if (isset($read_only)) {
			$this->read_only = $read_only;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->display);
	}

	//---------------------------------------------------------------------------------- htmlReadOnly
	/**
	 * @return string
	 */
	public function htmlReadOnly()
	{
		return $this->read_only ? 'checked' : '';
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean
	 */
	public function isEmpty()
	{
		return !(strval($this->display) || strval($this->path));
	}

}
