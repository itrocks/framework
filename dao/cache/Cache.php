<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\AOP\Joinpoint\Method_Joinpoint;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Uri;
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
class Cache implements Configurable, Registerable
{
	use Has_Get;

	//------------------------------------------------------------------------------- ENABLED_FOR_ALL
	const ENABLED_FOR_ALL = '*';

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
	 * If true, cache is enabled.
	 *
	 * @var boolean
	 */
	private $enabled;

	//------------------------------------------------------------------------------------- $features
	/**
	 * List of features to activate cache on / off.
	 *
	 * To activate cache on all features, use self::ENABLED_FOR_ALL. In this case, all other features
	 * are exceptions : cache is enabled on all features but this list.
	 *
	 * @var string[]
	 */
	private $features = Feature::READ_ONLY;

	//-------------------------------------------------------------------------------------- $maximum
	/**
	 * When there are more than MAXIMUM objects into the cache, let's purge PURGE of them
	 *
	 * @var integer
	 */
	private $maximum = 9999;

	//---------------------------------------------------------------------------------------- $purge
	/**
	 * @var integer
	 */
	private $purge = 2000;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Cache constructor
	 *
	 * - If no configuration or empty array : features default is 'all features cached'
	 * - If configuration set but no ['features' => ...] : features default is Feature::READ_ONLY
	 *
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		foreach ($config as $parameter => $value) {
			$this->$parameter = $value;
		}
		// enabled before executeController : true only if ENABLED_FOR_ALL
		$this->enabled = in_array(self::ENABLED_FOR_ALL, $this->features);
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
if (isset($GLOBALS['D'])) echo "CACHE add $class_name.$identifier" . BRLF;
			$this->cache[$class_name][$identifier] = new Cached($object);
			$this->count ++;
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
if (isset($GLOBALS['D'])) echo 'CACHE purge' . BRLF;
		$this->cache = [];
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
		if (!$this->enabled) {
			return null;
		}
		$class_name = Builder::className($class_name);
		if (isset($this->cache[$class_name][$identifier])) {
if (isset($GLOBALS['D'])) echo "CACHE get $class_name.$identifier" . BRLF;
			return $this->cache[$class_name][$identifier]->object;
		}
		return null;
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
				$counter ++;
				$list_id        = $cached->date->toISO() . '-' . sprintf($format, $counter);
				$list[$list_id] = [$class_name, $identifier];
			}
		}
		krsort($list);
		$threshold = $this->maximum - $this->purge;
		for (reset($list); $counter > $threshold; next($list)) {
			list($class_name, $identifier) = current($list);
if (isset($GLOBALS['D'])) echo "CACHE purge $class_name.$identifier" . BRLF;
			unset($this->cache[$class_name][$identifier]);
			$this->count --;
			$counter --;
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
		$aop->afterMethod ([Link::class, 'read'],              [$this, 'cacheReadObject']);
		$aop->afterMethod ([Link::class, 'write'],             [$this, 'cacheWriteObject']);
		$aop->beforeMethod([Link::class, 'read'],              [$this, 'get']);
		$aop->afterMethod ([Link::class, 'delete'],            [$this, 'removeObject']);
		$aop->beforeMethod([Main::class, 'executeController'], [$this, 'toggleCacheActivation']);
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
if (isset($GLOBALS['D'])) echo "CACHE remove $class_name.$identifier" . BRLF;
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

	//------------------------------------------------------------------------- toggleCacheActivation
	/**
	 * Toggles flag activation depending on controller's feature & configuration.
	 *
	 * @param $uri Uri
	 */
	public function toggleCacheActivation(Uri $uri)
	{
		$feature = $uri->feature_name;

		if (
			in_array(self::ENABLED_FOR_ALL, $this->features)
				? !in_array($feature, $this->features)
				: in_array($feature, $this->features)
		) {
if (isset($GLOBALS['D'])) echo 'CACHE toggle ON for ' . $uri . BRLF;
			$this->enabled = true;
		}
		else {
if (isset($GLOBALS['D'])) echo 'CACHE toggle OFF for ' . $uri . BRLF;
			$this->enabled = false;
			$this->flush();
		}
	}

}
