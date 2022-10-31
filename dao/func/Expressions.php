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
	public array $cache = [];

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var static
	 */
	public static Expressions $current;

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $property_path string
	 * @param $function      Column
	 * @return string The expression key in cache : always begin with '§' for an easy identification
	 */
	public static function add(string $property_path, Column $function) : string
	{
		$expression = new Expression($property_path, $function);
		$key        = static::MARKER . count(static::$current->cache);
		static::$current->cache[$key] = $expression;
		return $key;
	}

	//------------------------------------------------------------------------------------ isFunction
	/**
	 * Checks if a $property_path into a [$property_path => $value] Dao search expression begins with
	 * a MARKER, which means this is not a property path but the link to a function stored into
	 * Expressions' $cache
	 *
	 * @param $property_path string
	 * @return boolean
	 */
	public static function isFunction(string $property_path) : bool
	{
		return str_starts_with($property_path, static::MARKER);
	}

}

Expressions::$current = new Expressions();
