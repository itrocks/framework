<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * Cache where options and query result
 * And use cached result if exists
 */
class Cache_Result implements Option
{

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Results cache
	 *
	 * @var array object[string $class_name][string $where_hash][$result_number]
	 */
	protected array $cache;

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var static
	 */
	protected static Cache_Result $current;

	//----------------------------------------------------------------------------------- cacheResult
	/**
	 * Stores the result of a query into the cache
	 *
	 * @param $where      array|object
	 * @param $class_name string
	 * @param $options    Option[]
	 * @param $result     array
	 */
	public function cacheResult(
		array|object $where, string $class_name, array $options, array $result
	) {
		$options                                    = $this->hash($options);
		$where                                      = $this->hash($where);
		$this->cache[$class_name][$where][$options] = $result;
	}

	//---------------------------------------------------------------------------------- cachedResult
	/**
	 * Returns the cached result of a query
	 *
	 * @param $where      array|object
	 * @param $class_name string
	 * @param $options    Option[]
	 * @return object[]|null null if there is no cached result
	 */
	public function cachedResult(array|object $where, string $class_name, array $options) : ?array
	{
		if (isset($this->cache[$class_name])) {
			$options = $this->hash($options);
			$where   = $this->hash($where);
			if (isset($this->cache[$class_name][$where][$options])) {
				return $this->cache[$class_name][$where][$options];
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- clear
	/**
	 * Clear all or a part of the cache
	 *
	 * @param $class_name string|null
	 * @param $where      array|object|null
	 */
	public function clear(string $class_name = null, array|object $where = null)
	{
		if (!$class_name) {
			$this->cache = [];
		}
		elseif (!isset($where)) {
			unset($this->cache[$class_name]);
		}
		else {
			unset($this->cache[$class_name][$where]);
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @return static
	 */
	public static function current() : static
	{
		return static::$current ?? (static::$current = new static);
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets the Cache_Result instance (current) if $options contains a Cache_Result object
	 * If none, returns null
	 *
	 * @param $options Option[]
	 * @return ?static
	 */
	public static function get(array $options) : ?static
	{
		foreach ($options as $option) {
			if ($option instanceof static) {
				return static::current();
			}
		}
		return null;
	}

	//------------------------------------------------------------------------------------------ hash
	/**
	 * Calculates a hash for the given variable (array or object)
	 * If the array has no element, or the object is null, the hash will be ''
	 *
	 * @param $value array|object
	 * @return string
	 */
	protected function hash(array|object $value) : string
	{
		if (is_object($value)) {
			if (isset($value->id)) {
				return get_class($value) . ':' . $value->id;
			}
			return get_class($value) . ':' . $this->hash(get_object_vars($value));
		}
		foreach ($value as $key => $val) {
			$value[$key] = $key . ':' . $this->hash($val);
		}
		return '[' . join(',', $value) . ']';
	}

}
