<?php
namespace SAF\Framework;

/**
 * The autoload cache plugin  is here to make class autoload faster, but need update at each code update
 */
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
	 * @param $object     Autoloader
	 * @param $class_name string
	 */
	public function autoload($object, $class_name)
	{
		if ((strpos($class_name, "/") !== false) && isset(self::$full_class_names[$class_name])) {
			$class_name = self::$full_class_names[$class_name];
		}
		if (isset(self::$paths[$class_name])) {
			$object->includeClass($class_name, getcwd() . "/" . self::$paths[$class_name]);
		}
	}

	//--------------------------------------------------------------------------------- fullClassName
	/**
	 * @param $class_name string
	 * @return string
	 */
	public static function fullClassName($class_name)
	{
		return isset(self::$full_class_names[$class_name])
			? self::$full_class_names[$class_name]
			: $class_name;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the Autoload_Cache plugin
	 *
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		Application_Updater::addUpdatable($this);
		self::$cache_path = Application::current()->path->getSourceDirectory() . "/cache";
		/** @noinspection PhpIncludeInspection */
		@include self::$cache_path . "/autoload.php";
		if (!self::$paths || Application_Updater::mustUpdate()) {
			self::update();
		}
		$dealer = $register->dealer;
		$dealer->aroundMethodCall(
			array('SAF\Framework\Autoloader', "autoload"), array($this, "autoload")
		);
		$dealer->aroundMethodCall(
			array('SAF\Framework\Namespaces', "fullClassName"), array(__CLASS__, "fullClassName")
		);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Scans all PHP files into the project (excluding vendor) and store their paths to the cache
	 */
	public static function update()
	{
		$directories = Application::current()->getSourceFiles();
		self::$full_class_names = array();
		self::$paths = array();
		foreach ($directories as $file_path) {
			if (substr($file_path, -4) == ".php") {
				$buffer = file_get_contents($file_path);
				$short_class = trim(mParse($buffer, "\n" . "class ", "\n"))
					?: trim(mParse($buffer, "\n" . "final class ", "\n"))
					?: trim(mParse($buffer, "\n" . "abstract class ", "\n"))
					?: trim(mParse($buffer, "\n" . "final abstract class ", "\n"));
				if ($short_class) $type = "class";
				else {
					$short_class = trim(mParse($buffer, "\n" . "interface ", "\n"));
					if ($short_class) $type = "interface";
					else {
						$short_class = trim(mParse($buffer, "\n" . "trait ", "\n"));
						if ($short_class) $type = "trait";
					}
				}
				if ($short_class && isset($type)) {
					if ($i = strpos($short_class, " ")) {
						$short_class = substr($short_class, 0, $i);
					}
					$namespace = trim(mParse($buffer, "namespace ", ";"));
					$full_class = $namespace . "\\" . $short_class;
					if (($type == "class") && !isset(self::$full_class_names[$short_class])) {
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
			. 'SAF\Framework\Autoload_Cache::$full_class_names = '
			. var_export(self::$full_class_names, true) . ";\n"
			. "\n"
			. 'SAF\Framework\Autoload_Cache::$paths = '
			. var_export(self::$paths, true) . ";\n"
		);
	}

}
