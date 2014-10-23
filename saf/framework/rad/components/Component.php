<?php
namespace SAF\Framework\RAD\Components;

use SAF\Framework\Mapper;
use SAF\Framework\RAD\Features\Feature;

/**
 * A plugin component is used to store data or process, ie linked to a database
 *
 * @representative name
 */
class Component
{
	use Mapper\Component;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @composite
	 * @link Object
	 * @var Feature
	 */
	public $feature;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public $name;

	//---------------------------------------------------------------------------------------- $rules
	/**
	 * @link Collection
	 * @var Rule[]
	 */
	public $rules;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
