<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\AOP\Joinpoint\Method_Joinpoint;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Cache\Cached;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * DAO cache object
 */
class Cache implements Registerable, Configurable
{
	use Has_Get;

	//------------------------------------------------------------------------------- ENABLED_FOR_ALL
	const ENABLED_FOR_ALL = '*';

	//-------------------------------------------------------------------------------------- $maximum
	/**
	 * When there are more than MAXIMUM objects into the cache, let's purge PURGE of them
	 *
	 * @var integer
	 */
	private $maximum;

	//---------------------------------------------------------------------------------------- $purge
	/**
	 * @var integer
	 */
	private $purge;

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

	//-------------------------------------------------------------------------------------- $enabled
	/**
	 * If TRUE, cache is enabled.
	 *
	 * @var bool
	 */
	private $enabled;

	//------------------------------------------------------------------------------------- $features
	/**
	 * List of features to activate cache on.
	 * To activate cache on all features, use self::ENABLED_FOR_ALL.
	 *
	 * @var array|\string[]
	 */
	private $features;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Cache constructor.
	 *
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		$this->enabled  = true;
		$this->maximum  = $this->getValueOrDefault($config, 'maximum', 9999);
		$this->purge    = $this->getValueOrDefault($config, 'purge', 2000);
		$this->features = $this->getValueOrDefault(
			$config, 'features',
			[Feature::F_LIST, Feature::F_EDIT, Feature::F_EXPORT, Feature::F_OUTPUT]
		);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds/replaces an object to the cache
	 * If more than ::$maximum objects are stored, purge ::$purge objects
	 *
	 * @param $object object
	 * @param $link   Link
	 */
	public function add($object, Link $link = null)
	{
		// Do nothing if cache is disabled.
		if (!$this->enabled) {
			return;
		}

		if (!$link) {
			$link = Dao::current();
		}

		if (is_object($object) && ($identifier = $link->getObjectIdentifier($object))) {
			$class_name                            = Builder::className(get_class($object));
			$this->cache[$class_name][$identifier] = new Cached($object);
			$this->count++;
			if ($this->count > $this->maximum) {
				$this->purge();
			}
		}
	}

	//------------------------------------------------------------------------------- cacheReadObject
	/**
	 * Store object into cache on Dao::read()
	 *
	 * @param $result    object
	 * @param $joinpoint Method_Joinpoint
	 */
	public function cacheReadObject($result, Method_Joinpoint $joinpoint = null)
	{
		// Do nothing if cache is disabled.
		if (!$this->enabled) {
			return;
		}

		/** @var $link Link */
		$link = $joinpoint ? $joinpoint->object : Dao::current();
		$this->add($result, $link);
	}

	//------------------------------------------------------------------------------ cacheWriteObject
	/**
	 * Store object into cache on Dao::write(), if there is no write option
	 * (write option may suppose the object is incomplete)
	 *
	 * @param $object    object
	 * @param $options   Option|Option[]
	 * @param $joinpoint Method_Joinpoint
	 */
	public function cacheWriteObject($object, $options = [], Method_Joinpoint $joinpoint = null)
	{
		if ($this->enabled && !$options) {
			/** @var $link Link */
			$link = $joinpoint ? $joinpoint->object : Dao::current();
			$this->add($object, $link);
		}
	}

	//----------------------------------------------------------------------------------------- flush
	/**
	 * Flush cache
	 */
	public function flush()
	{
		$this->cache = [];
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Get cached object
	 *
	 * @param $class_name string
	 * @param $identifier integer
	 *
	 * @return object the cached object, null if none
	 */
	public function get($class_name, $identifier)
	{
		$class_name = Builder::className($class_name);
		return isset($this->cache[$class_name][$identifier])
			? $this->cache[$class_name][$identifier]->object
			: null;
	}

	//----------------------------------------------------------------------------- getValueOrDefault
	/**
	 * Tries to access value of $config[$key]. Returns it if exists, $default_value otherwise.
	 *
	 * @param array  $config        : The config array.
	 * @param string $key           : The key to access in config array.
	 * @param mixed  $default_value : Default value to return.
	 *
	 * @return mixed
	 */
	private function getValueOrDefault($config, $key, $default_value = null)
	{
		if (is_string($key) && is_array($config) && isset($config[$key])) {
			return $config[$key];
		}

		return $default_value;
	}

	//----------------------------------------------------------------------------------------- purge
	/**
	 * Purge removes ::$purge old objects from the cache
	 */
	private function purge()
	{
		$counter = 0;
		$format  = '%0' . strlen($this->maximum) . 's';
		$list    = [];
		foreach ($this->cache as $class_name => $cache) {
			foreach ($cache as $identifier => $cached) {
				/** @var $cached Cached */
				$counter++;
				$list_id        = $cached->date->toISO() . '-' . sprintf($format, $counter);
				$list[$list_id] = [$class_name, $identifier];
			}
		}
		krsort($list);
		$threshold = $this->maximum - $this->purge;
		for (reset($list); $counter > $threshold; next($list)) {
			list($class_name, $identifier) = current($list);
			unset($this->cache[$class_name][$identifier]);
			$this->count--;
			$counter--;
		}
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from the cache, knowing its class name and identifier
	 *
	 * @param $class_name string
	 * @param $identifier integer
	 */
	public function remove($class_name, $identifier)
	{
		$class_name = Builder::className($class_name);
		if (isset($this->cache[$class_name][$identifier])) {
			unset($this->cache[$class_name][$identifier]);
			$this->count--;
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
	 * @param Register $register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Link::class, 'read'], [$this, 'cacheReadObject']);
		$aop->afterMethod([Link::class, 'write'], [$this, 'cacheWriteObject']);
		$aop->beforeMethod([Link::class, 'read'], [$this, 'get']);
		$aop->afterMethod([Link::class, 'delete'], [$this, 'removeObject']);
		$aop->beforeMethod([Main::class, 'executeController'], [$this, 'toggleCacheActivation']);
	}

	//------------------------------------------------------------------------- toggleCacheActivation
	/**
	 * Toggles flag activation depending on controller's feature & configuration.
	 *
	 * @param Method_Joinpoint $joinpoint : The joinpoint object.
	 */
	public function toggleCacheActivation(Method_Joinpoint $joinpoint)
	{
		// Get feature's name.
		$feature = $joinpoint->parameters['uri']->feature_name;

		if (in_array($feature, $this->features) || $this->features === [self::ENABLED_FOR_ALL]) {
			$this->enabled = true;
		}
		else {
			// Disable cache.
			$this->enabled = false;

			// And flush it.
			$this->flush();
		}
	}

}
