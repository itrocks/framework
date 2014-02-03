<?php
namespace SAF\Framework;

/**
 * The autoload cache plugin  is here to make class autoload faster, but need update at each code update
 */
class Autoload_Cache implements Activable_Plugin, Updatable
{

	//----------------------------------------------------------------------------------- $cache_file
	/**
	 * @var string
	 */
	public $cache_path;

	//----------------------------------------------------------------------------- $full_class_names
	/**
	 * @var string[]
	 */
	public $full_class_names = array();

	//---------------------------------------------------------------------------------------- $paths
	/**
	 * @var string[]
	 */
	public $paths = array();

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		/** @var $application_updater Application_Updater */
		$application_updater = Session::current()->plugins->getPlugin(
			'SAF\Framework\Application_Updater'
		);
		$application_updater->addUpdatable($this);
		$this->cache_path = Application::current()->include_path->getSourceDirectory() . "/cache";
		/** @noinspection PhpIncludeInspection */
		@include $this->cache_path . "/autoload.php";
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
		if ((strpos($class_name, "/") !== false) && isset($this->full_class_names[$class_name])) {
			$class_name = $this->full_class_names[$class_name];
		}
		if (isset($this->paths[$class_name])) {
			$object->includeClass($class_name, getcwd() . "/" . $this->paths[$class_name]);
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
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		$dealer = $register->dealer;
		$dealer->aroundMethodCall(
			array('SAF\Framework\Autoloader', "autoload"), array($this, "autoload")
		);
		$dealer->aroundMethodCall(
			array('SAF\Framework\Namespaces', "fullClassName"), array($this, "fullClassName")
		);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Scans all PHP files into the project (excluding vendor) and store their paths to the cache
	 */
	public function update()
	{
		$directories = Application::current()->getSourceFiles();
		$this->full_class_names = array();
		$this->paths = array();
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
					$namespace = trim(mParse($buffer, "\n" . "namespace ", "\n"));
					if (substr($namespace, -1) == ";") {
						$namespace = trim(substr($namespace, 0, -1));
					}
					$full_class = $namespace . "\\" . $short_class;
					if (($type == "class") && !isset($this->full_class_names[$short_class])) {
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
		file_put_contents(
			$this->cache_path . "/autoload.php",
			"<?php\n\n"
			. '$this->full_class_names = '
			. var_export($this->full_class_names, true) . ";\n"
			. "\n"
			. '$this->paths = '
			. var_export($this->paths, true) . ";\n"
		);
	}

}
