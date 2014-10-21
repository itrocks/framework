<?php
namespace SAF\Framework\RAD;

use SAF\Framework\Mapper;

/**
 * A rule is the atomic part of settings
 *
 * @set RAD_Rules
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

}
