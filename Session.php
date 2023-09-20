<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Plugin\Manager;
use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * A class to manage variables and objects that are kept for the session time
 */
class Session
{

	//----------------------------------------------------- PLUGINS Serialize / unserialize constants
	const CONFIGURATION_FILE_NAME = 'configuration_file_name';
	const PLUGINS                 = 'plugins';

	//--------------------------------------------------------------------------------------- CURRENT
	/** A 'current' constant used for array storage / current method dynamic calls */
	const CURRENT = 'current';

	//---------------------------------------------------------------------- $configuration_file_name
	public string $configuration_file_name;

	//-------------------------------------------------------------------------------------- $current
	/** @var object[]|string[] */
	private array $current;

	//--------------------------------------------------------------------------------------- $domain
	/** Same as Configuration::$domain */
	public string $domain;

	//---------------------------------------------------------------------------------- $environment
	/** Same as Configuration::$environment */
	#[Values('development, production, test')]
	public string $environment;

	//-------------------------------------------------------------------------------------- $plugins
	public Manager $plugins;

	//-------------------------------------------------------------------------------------- $stopped
	/**
	 * When true, the session will be closed at the end of the script execution.
	 * This is the case when an action disconnects the action : all session data will be reset,
	 * including the session id.
	 *
	 * Do not set this directly to true : call stop()
	 *
	 * @see stop()
	 */
	public bool $stopped = false;

	//-------------------------------------------------------------------------- $temporary_directory
	/**
	 * The application temporary directory that you can get using
	 * Application::current()->getTemporaryFilesPath()
	 *
	 * Default will be /tmp/Application_Class_Name
	 */
	public ?string $temporary_directory;

	//----------------------------------------------------------------------------------- __serialize
	public function __serialize() : array
	{
		$data = [
			self::CONFIGURATION_FILE_NAME      => $this->configuration_file_name,
			self::CURRENT                      => [],
			self::PLUGINS                      => $this->plugins,
			Configuration::DOMAIN              => $this->domain,
			Configuration::ENVIRONMENT         => $this->environment,
			Configuration::TEMPORARY_DIRECTORY => $this->temporary_directory
		];
		if (isset($this->current)) {
			foreach ($this->current as $class_name => $object) {
				if (is_object($object)) {
					$object = [$class_name, Dao::getObjectIdentifier($object) ?: serialize($object)];
				}
				$data[self::CURRENT][$class_name] = $object;
			}
		}
		return $data;
	}

	//--------------------------------------------------------------------------------- __unserialize
	public function __unserialize(array $serialized) : void
	{
		$data = $serialized;
		$this->configuration_file_name = $data[self::CONFIGURATION_FILE_NAME];
		$this->current                 = $data[self::CURRENT];
		$this->domain                  = $data[Configuration::DOMAIN] ?? null;
		$this->environment             = $data[Configuration::ENVIRONMENT];
		$this->plugins                 = $data[self::PLUGINS];
		$this->temporary_directory     = $data[Configuration::TEMPORARY_DIRECTORY];
	}

	//-------------------------------------------------------------------------------- cloneSessionId
	/**
	 * Returns a cloned session id
	 *
	 * This feature enables session data cloning, and is useful when you want to call scripts using
	 * localhost keeping your actual session opened.
	 *
	 * What is done by cloneSid :
	 * - a new session id is registered
	 * - the new session file is immediately created with the data of the current session
	 *
	 * @return string the cloned session id
	 */
	public static function cloneSessionId() : string
	{
		$new_id = lParse(session_id(), '-') . uniqid('-');
		file_put_contents(session_save_path() . SL . 'sess_' . $new_id, session_encode());
		return $new_id;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current static|null
	 * @return ?static
	 */
	public static function current(self $set_current = null) : ?static
	{
		if ($set_current) {
			$_SESSION['session'] = $set_current;
			return $set_current;
		}
		return $_SESSION['session'] ?? null;
	}

	//------------------------------------------------------------------------------------ domainName
	/** @example itrocks.org */
	public function domainName() : string
	{
		return str_contains($this->domain, '://') ? parse_url($this->domain)['host'] : $this->domain;
	}

	//------------------------------------------------------------------------------------ domainPath
	/** @example application */
	public function domainPath() : string
	{
		return str_contains($this->domain, '://') ? parse_url($this->domain)['path'] : '';
	}

	//---------------------------------------------------------------------------------- domainScheme
	/** @example http, https */
	public function domainScheme() : string
	{
		return (str_contains($this->domain, '://') ? parse_url($this->domain)['scheme'] : '')
			?: 'https';
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Get the object of class $class_name from session
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name     class-string<T>|string
	 * @param $create_default boolean|callable Create a default object for the class name if it does
	 *                                         not
	 *        exist. Can be callable that creates the default object
	 * @return mixed|T
	 * @template T
	 */
	public function get(string $class_name, bool|callable $create_default = false) : mixed
	{
		if (!isset($this->current[$class_name])) {
			$class_name = Builder::current()->sourceClassName($class_name);
		}
		if (isset($this->current[$class_name])) {
			$current = $this->current[$class_name];
			if (is_array($current) && class_exists($class_name)) {
				$current = $current[1];
				$this->current[$class_name] = $current = (
					is_numeric($current)
						? Dao::read($current, $class_name)
						: unserialize($current)
				);
			}
			return $current;
		}
		elseif ($create_default) {
			/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
			return $this->current[$class_name] = (
				is_callable($create_default)
					? call_user_func($create_default)
					: Builder::create($class_name)
			);
		}
		else {
			return null;
		}
	}

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Get all objects from session
	 *
	 * @return object[] index is class name, value is an object
	 */
	public function getAll() : array
	{
		return $this->current;
	}

	//---------------------------------------------------------------------------------------- getAny
	/**
	 * Get all objects from session having $class_name as class or parent class
	 *
	 * @param $class_name string
	 * @return object[] key is the class name of the object
	 */
	public function getAny(string $class_name) : array
	{
		$get = [];
		foreach ($this->getAll() as $key => $value) {
			if (isset(class_parents($key)[$class_name])) {
				$get[$key] = $value;
			}
		}
		return $get;
	}

	//---------------------------------------------------------------------------- getApplicationName
	/** Gets the current application name without having to unserialize it if serialized */
	public function getApplicationName() : string
	{
		$current = $this->current[Application::class];
		// TODO parse current[1] between '"' and replace array with string if R work well
		$class_name = is_array($current) ? $current[0] : get_class($current);
		$application_name = substr($class_name, 0, strrpos($class_name, BS));
		return strtolower(substr($application_name, strrpos($application_name, BS) + 1));
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from session
	 *
	 * @param $object_class object|string
	 */
	public function remove(object|string $object_class) : void
	{
		unset($this->current[is_string($object_class) ? $object_class : get_class($object_class)]);
	}

	//------------------------------------------------------------------------------------- removeAny
	/**
	 * Remove any session variable that has $object_class as class or parent class
	 *
	 * @param $object_class object|string
	 */
	public function removeAny(object|string $object_class) : void
	{
		$class_name = is_string($object_class) ? $object_class : get_class($object_class);
		$this->remove($class_name);
		foreach ($this->getAll() as $key => $value) {
			if (class_exists($key) && isset(class_parents($key)[$class_name])) {
				$this->remove($key);
			}
		}
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Set a session's object
	 *
	 * @param $object     object|mixed can be null (then nothing is set)
	 * @param $class_name string|null if not set, object class is the object identifier.
	 *                    Can be a free string too
	 */
	public function set(mixed $object, string $class_name = null) : void
	{
		if (isset($object)) {
			$class_name = Builder::current()->sourceClassName($class_name ?? get_class($object));
			$this->current[$class_name] = $object;
		}
	}

	//------------------------------------------------------------------------------------------- sid
	/**
	 * Returns current SID
	 *
	 * @example 'PHPSESSID=6kldcf5gbuk0u34cmihlo9gl22'
	 * @param $prefix string You can prefix your SID with '?' or '&' to append it to a URI or URL
	 * @return string
	 */
	public static function sid(string $prefix = '') : string
	{
		return session_id() ? ($prefix . session_name() . '=' . session_id()) : '';
	}

	//------------------------------------------------------------------------------------------ stop
	/**
	 * Stops the current session.
	 *
	 * This will destroy the session data at the end of the script.
	 * The session cookie will be removed so that a new session is created at next click.
	 */
	public function stop() : void
	{
		$params = session_get_cookie_params();
		if ($_COOKIE[session_name()] === session_id()) {
			setcookie(
				session_name(), '', time() - 42000,
				$params['path'], $params['domain'], $params['secure'], $params['httponly']
			);
		}
		session_destroy();
		$this->stopped = true;
	}

}
