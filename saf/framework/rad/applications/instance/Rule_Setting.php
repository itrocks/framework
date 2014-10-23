<?php
namespace SAF\Framework\RAD\Applications\Instance;

use SAF\Framework\Mapper;
use SAF\Framework\RAD\Components\Rule;

/**
 * Rule setting
 *
 * @link Rule
 * @set Rules_Settings
 */
class Rule_Setting
{
	use Mapper\Component;

	//--------------------------------------------------------------------------------------- $plugin
	/**
	 * @composite
	 * @link Object
	 * @var Active_Plugin
	 */
	public $plugin;

	//----------------------------------------------------------------------------------------- $rule
	/**
	 * @composite
	 * @link Object
	 * @var Rule
	 */
	private /* @noinspection PhpUnusedPrivateFieldInspection @link */ $rule;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed
	 */
	public $value;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->rule->name . SP . '=' . SP . $this->value;
	}

}
