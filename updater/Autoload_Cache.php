<?php
namespace ITRocks\Framework\Updater;

use ITRocks\Framework\Application;
use ITRocks\Framework\Autoloader;
use ITRocks\Framework\Plugin\Activable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Namespaces;

/**
 * The autoload cache plugin  is here to make class autoload faster, but need update at each code
 * update.
 * It has been replaced by AOP and compilers
 *
 * @deprecated
 */
class Autoload_Cache implements Activable, Updatable
{

	//----------------------------------------------------------------------------------- $cache_path
	/**
	 * @var string
	 */
	public $cache_path;

	//----------------------------------------------------------------------------- $full_class_names
	/**
	 * @var string[]
	 */
	public $full_class_names = [];

	//---------------------------------------------------------------------------------------- $paths
	/**
	 * @var string[]
	 */
	public $paths = [];

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		/** @var $application_updater Application_Updater */
		$application_updater = Session::current()->plugins->get(Application_Updater::class);
		$application_updater->addUpdatable($this);
		$this->cache_path = Application::current()->include_path->getSourceDirectory() . '/cache';
		/** @noinspection PhpIncludeInspection */
		@include $this->cache_path . '/autoload.php';
		if (!$this->paths || !$this->full_class_names || $application_updater->mustUpdate()) {
			$this->update();
		}
	}

	//-------------------------------------------------------------------------------------- autoload
	/**
	 * @param $object     Autoloader
	 * @param $class_name string
	 */
	public function autoload($object, $class_name)
	{
		if ((strpos($class_name, SL) !== false) && isset($this->full_class_names[$class_name])) {
			$class_name = $this->full_class_names[$class_name];
		}
		if (isset($this->paths[$class_name])) {
			$object->includeClass($class_name, getcwd() . SL . $this->paths[$class_name]);
		}
	}

	//--------------------------------------------------------------------------------- fullClassName
	/**
	 * @param $class_name string
	 * @return string
	 */
	public function fullClassName($class_name)
	{
		return isset($this->full_class_names[$class_name])
			? $this->full_class_names[$class_name]
			: $class_name;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the Autoload_Cache plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->aroundMethod([Autoloader::class, 'autoload'],      [$this, 'autoload']);
		$aop->aroundMethod([Namespaces::class, 'fullClassName'], [$this, 'fullClassName']);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Scans all PHP files into the project (excluding vendor) and store their paths to the cache
	 */
	public function update($last_time = 0)
	{
		$files = Application::current()->include_path->getSourceFiles();
		$this->full_class_names = [];
		$this->paths = [];
		foreach ($files as $file_path) {
			if (substr($file_path, -4) == '.php') {
				$buffer = file_get_contents($file_path);
				$short_class = trim(mParse($buffer, LF . 'class' . SP, LF))
					?: trim(mParse($buffer, LF . 'final class' . SP, LF))
					?: trim(mParse($buffer, LF . 'abstract class' . SP, LF))
					?: trim(mParse($buffer, LF . 'final abstract class' . SP, LF));
				if ($short_class) $type = 'class';
				else {
					$short_class = trim(mParse($buffer, LF . 'interface' . SP, LF));
					if ($short_class) $type = 'interface';
					else {
						$short_class = trim(mParse($buffer, LF . 'trait' . SP, LF));
						if ($short_class) $type = 'trait';
					}
				}
				if ($short_class && isset($type)) {
					if ($i = strpos($short_class, SP)) {
						$short_class = substr($short_class, 0, $i);
					}
					$namespace = trim(mParse($buffer, LF . 'namespace' . SP, LF));
					if (substr($namespace, -1) == ';') {
						$namespace = trim(substr($namespace, 0, -1));
					}
					$full_class = $namespace . BS . $short_class;
					if (($type == 'class') && !isset($this->full_class_names[$short_class])) {
						$this->full_class_names[$short_class] = $full_class;
					}
					if (!isset($this->paths[$full_class])) {
						$this->paths[$full_class] = $file_path;
					}
				}
			}
		}
		if (!is_dir($this->cache_path)) {
			mkdir($this->cache_path);
		}
		script_put_contents(
			$this->cache_path . SL . 'autoload.php',
			'<?php' . LF . LF
			. '$this->full_class_names = '
			. var_export($this->full_class_names, true) . ';' . LF
			. LF
			. '$this->paths = '
			. var_export($this->paths, true) . ';' . LF
		);
	}

}
