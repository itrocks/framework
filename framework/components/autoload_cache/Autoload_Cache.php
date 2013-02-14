<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Autoload_Cache implements Plugin, Updatable
{

	//----------------------------------------------------------------------------------- $cache_file
	/**
	 * @var string
	 */
	public static $cache_path;

	//----------------------------------------------------------------------------- $full_class_names
	/**
	 * @var string[]
	 */
	public static $full_class_names = array();

	//---------------------------------------------------------------------------------------- $paths
	/**
	 * @var string[]
	 */
	public static $paths = array();

	//-------------------------------------------------------------------------------------- autoload
	/**
	 * Autoload replacement, with cache
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function autoload(AopJoinpoint $joinpoint)
	{
		$class_name = $joinpoint->getArguments()[0];
		if ((strpos($class_name, "/") !== false) && isset(self::$full_class_names[$class_name])) {
			$class_name = self::$full_class_names[$class_name];
		}
		if (isset(self::$paths[$class_name])) {
			include_once self::$paths[$class_name];
			$joinpoint->setReturnedValue(Namespaces::shortClassName($class_name));
			Autoloader::classLoadEvent($class_name);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the Autoload_Cache plugin
	 */
	public static function register()
	{
		Application_Updater::addUpdatable(get_called_class());
		self::$cache_path = strtolower(Configuration::current()->getApplicationName()) . "/cache";
		include(self::$cache_path . "/autoload.php");
		if (!self::$paths) {
			self::update();
		}
		Aop::add("around", 'SAF\Framework\Autoloader->autoload()', array(__CLASS__, "autoload"));
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Scans all PHP files into the project (excluding vendor) and store their paths to the cache
	 */
	public static function update()
	{
		$application_name = Configuration::current()->getApplicationName();
		$directories = Application::getSourceFiles($application_name);
		self::$paths = array();
		foreach ($directories as $file_path) {
			if (substr($file_path, -4) == ".php") {
				$buffer = file_get_contents($file_path);
				$namespace = trim(mParse($buffer, "namespace ", ";"));
				$short_class = trim(mParse($buffer, "\nclass ", "\n"));
				if (!$short_class) {
					$short_class = trim(mParse($buffer, "\nabstract class ", "\n"));
				}
				if (!$short_class) {
					$short_class = trim(mParse($buffer, "\ninterface ", "\n"));
				}
				if (!$short_class) {
					$short_class = trim(mParse($buffer, "\ntrait ", "\n"));
				}
				if ($i = strpos($short_class, " ")) {
					$short_class = substr($short_class, 0, $i);
				}
				if ($short_class) {
					$full_class = $namespace . "\\" . $short_class;
					if (!isset(self::$full_class_names[$short_class])) {
						self::$full_class_names[$short_class] = $full_class;
					}
					if (!isset(self::$paths[$full_class])) {
						self::$paths[$full_class] = $file_path;
					}
				}
			}
		}
		if (!is_dir(self::$cache_path)) {
			mkdir(self::$cache_path);
		}
		file_put_contents(
			self::$cache_path . "/autoload.php",
			"<?php\n\n"
			. "SAF\\Framework\\Autoload_Cache::\$full_class_names = "
			. var_export(self::$full_class_names, true) . ";\n"
			. "\n"
			. "SAF\\Framework\\Autoload_Cache::\$paths = "
			. var_export(self::$paths, true) . ";\n"
		);
	}

}
