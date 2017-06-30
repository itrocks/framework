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
	protected $cache;

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var static
	 */
	protected static $current;

	//----------------------------------------------------------------------------------- cacheResult
	/**
	 * Stores the result of a query into the cache
	 *
	 * @param $where      array|object
	 * @param $class_name string
	 * @param $options    Option[]
	 * @param $result     array
	 */
	public function cacheResult($where, $class_name, array $options, array $result)
	{
		$options = $this->hash($options);
		$where   = $this->hash($where);
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
	public function cachedResult($where, $class_name, array $options)
	{
		if (isset($this->cache[$class_name])) {
			$options = $this->hash($options);
			$where   = $this->hash($where);
			if (isset($this->cache[$class_name][$where][$options])) {
				$result = $this->cache[$class_name][$where][$options];
				return $result;
			}
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- clear
	/**
	 * Clear all or a part of the cache
	 *
	 * @param $class_name string
	 * @param $where      array|object
	 */
	public function clear($class_name = null, $where = null)
	{
		if (!$class_name) {
			$this->cache = [];
		}
		elseif (!$where) {
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
	public static function current()
	{
		return isset(static::$current) ? static::$current : (static::$current = new static);
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Gets the Cache_Result instance (current) if $options contains a Cache_Result object
	 * If none, returns null
	 *
	 * @param $options Option[]
	 * @return static|null
	 */
	public static function get(array $options)
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
	protected function hash($value)
	{
		if (is_object($value)) {
			if (isset($value->id)) {
				return get_class($value) . ':' . $value->id;
			}
			else {
				return get_class($value) . ':' . $this->hash(get_object_vars($value));
			}
		}
		if (is_array($value)) {
			foreach ($value as $key => $val) {
				$value[$key] = $key . ':' . $this->hash($val);
			}
			return '[' . join(',', $value) . ']';
		}
		return $value;
	}

}
