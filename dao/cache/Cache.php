<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Cache\Cached;
use ITRocks\Framework\Dao\Mysql;
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
	 * @var object[][] keys are [$class_name string][$identifier integer|string], value is a Cached
	 */
	protected array $cache = [];

	//---------------------------------------------------------------------------------------- $count
	/**
	 * @var integer
	 */
	protected int $count = 0;

	//-------------------------------------------------------------------------------------- $enabled
	/**
	 * If true, cache is enabled.
	 *
	 * @var boolean
	 */
	protected bool $enabled;

	//------------------------------------------------------------------------------------- $features
	/**
	 * List of features to activate cache on / off.
	 *
	 * To activate cache on all features, use self::ENABLED_FOR_ALL. In this case, all other features
	 * are exceptions : cache is enabled on all features but this list.
	 *
	 * @var string[]
	 */
	protected array $features = Feature::READ_ONLY;

	//-------------------------------------------------------------------------------------- $maximum
	/**
	 * When there are more than MAXIMUM objects into the cache, let's purge 'PURGE' of them
	 *
	 * @var integer
	 */
	protected int $maximum = 9999;

	//---------------------------------------------------------------------------------------- $purge
	/**
	 * @var integer
	 */
	protected int $purge = 2000;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Cache constructor
	 *
	 * - If no configuration or empty array : features default is 'all features cached'
	 * - If configuration set but no ['features' => ...] : features default is Feature::READ_ONLY
	 *
	 * @param $config array
	 */
	public function __construct(mixed $config = [])
	{
		foreach ($config as $parameter => $value) {
			$this->$parameter = $value;
		}
		// enabled before executeController : true only if ENABLED_FOR_ALL
		$this->enabled = in_array(self::ENABLED_FOR_ALL, $this->features, true);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds/replaces an object to the cache
	 * If more than ::$maximum objects are stored, purge ::$purge objects
	 *
	 * @param $object object
	 * @param $link   Mysql\Link|null
	 */
	public function add(object $object, Mysql\Link $link = null) : void
	{
		if (!$this->enabled) {
			return;
		}
		if (!($identifier = ($link ?: Dao::current())->getObjectIdentifier($object))) {
			return;
		}
		$class_name = Builder::className(get_class($object));
		if (isset($GLOBALS['D'])) echo "CACHE add $class_name.$identifier" . BRLF;
		$this->cache[$class_name][$identifier] = new Cached($object);
		$this->count ++;
		if ($this->count > $this->maximum) {
			$this->purge();
		}
	}

	//------------------------------------------------------------------------------- cacheReadObject
	/**
	 * Store object into cache on Dao::read()
	 *
	 * @param $joinpoint After_Method
	 */
	public function cacheReadObject(After_Method $joinpoint) : void
	{
		if (!$this->enabled) {
			return;
		}
		if ($joinpoint->result) {
			$this->add($joinpoint->result, $joinpoint->object);
		}
	}

	//------------------------------------------------------------------------------ cacheWriteObject
	/**
	 * Store object into cache on Dao::write(), if there is no write option
	 * (write option may suppose the object is incomplete)
	 *
	 * @param $object    object
	 * @param $options   Option|Option[]
	 * @param $joinpoint After_Method
	 */
	public function cacheWriteObject(
		object $object, array|Option $options, After_Method $joinpoint
	) : void
	{
		if ($options || !$this->enabled) {
			return;
		}
		$this->add($object, $joinpoint->object);
	}

	//---------------------------------------------------------------------------------------- enable
	/**
	 * Enable / disable the cache
	 *
	 * @param $enable boolean Cache will be enabled if true, or disabled if false
	 * @return boolean true if was enabled before call, else false
	 */
	public function enable(bool $enable = true) : bool
	{
		$enabled       = $this->enabled;
		$this->enabled = $enable;
		return $enabled;
	}

	//----------------------------------------------------------------------------------------- flush
	/**
	 * Flush cache
	 */
	public function flush() : void
	{
		if (isset($GLOBALS['D'])) echo 'CACHE purge' . BRLF;
		$this->cache = [];
	}

	//------------------------------------------------------------------------------- getCachedObject
	/**
	 * Get cached object
	 *
	 * @param $class_name class-string<T>
	 * @param $identifier int|string|T identifier for the object, or an object to re-read
	 * @return ?T the cached object, null if none
	 * @template T
	 */
	public function getCachedObject(string $class_name, int|object|string $identifier) : ?object
	{
		if (!$this->enabled) {
			return null;
		}
		$class_name = Builder::className($class_name);
		if (is_object($identifier)) {
			$identifier = Dao::getObjectIdentifier($identifier);
		}
		return $this->cache[$class_name][$identifier]->object ?? null;
	}

	//----------------------------------------------------------------------------------------- purge
	/**
	 * Purge removes ::$purge old objects from the cache
	 */
	private function purge() : void
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
		for (; $counter > $threshold; next($list)) {
			[$class_name, $identifier] = current($list);
			if (isset($GLOBALS['D'])) echo "CACHE purge $class_name.$identifier" . BRLF;
			unset($this->cache[$class_name][$identifier]);
			$this->count --;
			$counter --;
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod ([Mysql\Link::class, 'read'],              [$this, 'cacheReadObject']);
		$aop->afterMethod ([Mysql\Link::class, 'write'],             [$this, 'cacheWriteObject']);
		$aop->beforeMethod([Mysql\Link::class, 'read'],              [$this, 'getCachedObject']);
		$aop->afterMethod ([Mysql\Link::class, 'delete'],            [$this, 'removeObject']);
		$aop->beforeMethod([Main::class, 'executeController'], [$this, 'toggleCacheActivation']);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from the cache, knowing its class name and identifier
	 *
	 * @param $class_name string
	 * @param $identifier integer|string
	 */
	public function remove(string $class_name, int|string $identifier) : void
	{
		$class_name = Builder::className($class_name);
		if (isset($this->cache[$class_name][$identifier])) {
			if (isset($GLOBALS['D'])) echo "CACHE remove $class_name.$identifier" . BRLF;
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
	 * @param $joinpoint After_Method
	 */
	public function removeObject(object $object, After_Method $joinpoint) : void
	{
		/** @var $link Mysql\Link */
		$link = $joinpoint->object;
		if ($joinpoint->result && ($identifier = $link->getObjectIdentifier($object))) {
			$this->remove(get_class($object), $identifier);
		}
	}

	//------------------------------------------------------------------------- toggleCacheActivation
	/**
	 * Toggles flag activation depending on controller's feature & configuration.
	 *
	 * @param $uri Uri
	 */
	public function toggleCacheActivation(Uri $uri) : void
	{
		$feature = $uri->feature_name;
		if (
			in_array(self::ENABLED_FOR_ALL, $this->features, true)
				? !in_array($feature, $this->features, true)
				: in_array($feature, $this->features, true)
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
