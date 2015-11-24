<?php
namespace SAF\Framework\Dao;

use SAF\Framework\AOP\Joinpoint\Method_Joinpoint;
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Cache\Cached;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Dao\Option;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;

/**
 * DAO cache object
 */
class Cache implements Registerable
{

	/**
	 * When there are more than MAXIMUM objects into the cache, let's purge PURGE of them
	 */
	const MAXIMUM = 9999;
	const PURGE   = 2000;

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * @var array keys are [$class_name string][$identifier integer], value is a Cached
	 */
	private $cache = [];

	//---------------------------------------------------------------------------------------- $count
	/**
	 * @var integer
	 */
	private $count = 0;

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds/replaces an object to the cache
	 * If more than MAXIMUM objects are stored, purge PURGE objects
	 *
	 * @param $object object
	 * @param $link   Link
	 */
	public function add($object, Link $link = null)
	{
		if (!$link) {
			$link = Dao::current();
		}
		if (is_object($object) && ($identifier = $link->getObjectIdentifier($object))) {
			$class_name = Builder::className(get_class($object));
			$this->cache[$class_name][$identifier] = new Cached($object);
			$this->count++;
			if ($this->count > self::MAXIMUM) {
				$this->purge();
			}
		}
	}

	//------------------------------------------------------------------------------- cacheReadObject
	/**
	 * Keep object into cache on write, if there is no write option
	 * (write option may suppose the object is incomplete)
	 *
	 * @param $result    object
	 * @param $joinpoint Method_Joinpoint
	 */
	public function cacheReadObject($result, Method_Joinpoint $joinpoint = null)
	{
		/** @var $link Link */
		$link = $joinpoint ? $joinpoint->object : Dao::current();
		$this->add($result, $link);
	}

	//------------------------------------------------------------------------------ cacheWriteObject
	/**
	 * Keep object into cache on write, if there is no write option
	 * (write option may suppose the object is incomplete)
	 *
	 * @param $object    object
	 * @param $options   Option[]
	 * @param $joinpoint Method_Joinpoint
	 */
	public function cacheWriteObject($object, $options = [], Method_Joinpoint $joinpoint = null)
	{
		if (!$options) {
			/** @var $link Link */
			$link = $joinpoint ? $joinpoint->object : Dao::current();
			$this->add($object, $link);
		}
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Get cached object
	 *
	 * @param $class_name string
	 * @param $identifier integer
	 * @return object the cached object, null if none
	 */
	public function get($class_name, $identifier)
	{
		$class_name = Builder::className($class_name);
		return isset($this->cache[$class_name][$identifier])
			? $this->cache[$class_name][$identifier]->object
			: null;
	}

	//----------------------------------------------------------------------------------------- purge
	private function purge()
	{
		$counter = 0;
		$format = '%0' . strlen(self::MAXIMUM) . 's';
		$list = [];
		foreach ($this->cache as $class_name => $cache) {
			foreach ($cache as $identifier => $cached) {
				/** @var $cached Cached */
				$counter ++;
				$list_id = $cached->date->toISO() . '-' . sprintf($format, $counter);
				$list[$list_id] = [$class_name, $identifier];
			}
		}
		krsort($list);
		$threshold = self::MAXIMUM - self::PURGE;
		for (reset($list); $counter > $threshold; next($list)) {
			list($class_name, $identifier) = current($list);
			unset($this->cache[$class_name][$identifier]);
			$this->count --;
		}
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove from cache
	 *
	 * @param $class_name string
	 * @param $identifier integer
	 */
	public function remove($class_name, $identifier)
	{
		$class_name = Builder::className($class_name);
		if (isset($this->cache[$class_name][$identifier])) {
			unset($this->cache[$class_name][$identifier]);
			$this->count --;
		}
	}

	//---------------------------------------------------------------------------------- removeObject
	/**
	 * Remove an object from the cache
	 * Is called each time an object is deleted from the DAO
	 *
	 * @param $object    object
	 * @param $joinpoint Method_Joinpoint
	 */
	public function removeObject($object, Method_Joinpoint $joinpoint = null)
	{
		/** @var $link Link */
		$link = $joinpoint ? $joinpoint->object : Dao::current();
		if (
			is_object($object)
			&& ($identifier = $link->getObjectIdentifier($object))
			&& (!$joinpoint || $joinpoint->result)
		) {
			$this->remove(get_class($object), $identifier);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Link::class, 'read'], [$this, 'cacheReadObject']);
		$aop->afterMethod([Link::class, 'write'], [$this, 'cacheWriteObject']);
		$aop->beforeMethod([Link::class, 'read'], [$this, 'get']);
		$aop->afterMethod([Link::class, 'delete'], [$this, 'removeObject']);
	}

}
