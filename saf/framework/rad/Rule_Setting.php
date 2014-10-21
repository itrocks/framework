<?php
namespace SAF\Framework\RAD;

use SAF\Framework\Mapper;
use SAF\Framework\RAD\Plugin\Active_Plugin;

/**
 * Rule setting
 *
 * @link Rule
 * @set RAD_Rules_Settings
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

}
