<?php
namespace SAF\Framework\RAD\Components;

use SAF\Framework\Mapper;

/**
 * A rule is the atomic part of settings
 */
class Rule
{
	use Mapper\Component;

	//------------------------------------------------------------------------------------ $component
	/**
	 * @composite
	 * @link Object
	 * @var Component
	 */
	public $component;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @mandatory
	 * @values boolean|string
	 * @var string
	 */
	public $type;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
