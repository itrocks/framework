<?php
namespace ITRocks\Framework\Dao\Func;

/**
 * Expressions cache singleton object
 */
class Expressions
{

	//---------------------------------------------------------------------------------------- MARKER
	const MARKER = '§';

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Please use add() to easily add an expression to the cache
	 * You can read $cache directly, the key is the string value returned by add() and begins with '¤'
	 *
	 * @var Expression[] ['§$count' => Expression]
	 */
	public $cache = [];

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var static
	 */
	public static $current;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $property_path string
	 * @param $function      Column
	 * @return string The expression key in cache : always begin with '§' for an easy identification
	 */
	public static function add($property_path, Column $function)
	{
		$expression = new Expression($property_path, $function);
		$key        = static::MARKER . count(static::$current->cache);
		static::$current->cache[$key] = $expression;
		return $key;
	}

}

Expressions::$current = new Expressions();
