<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/configuration/Configuration.php";
require_once "framework/core/toolbox/String.php";

class Application
{

	//----------------------------------------------------------------------------------- $namespaces
	/**
	 * Namespaces list cache : initialized at first use
	 *
	 * @var string[]
	 */
	public static $namespaces = array();

	//-------------------------------------------------------------------------------- getDirectories
	/**
	 * This is called by getSourceDirectories() for recursive directories reading
	 *
	 * @param $path string base path
	 * @return string[] an array of directories names
	 */
	private static function getDirectories($path)
	{
		$directories = array($path);
		$dir = dir($path);
		while ($entry = $dir->read()) if ($entry[0] != ".") {
			if (is_dir("$path/$entry")) {
				$directories = array_merge($directories, static::getDirectories("$path/$entry"));
			}
		}
		return $directories;
	}

	//-------------------------------------------------------------------------------------- getFiles
	/**
	 * This is called by getSourceFiles() for recursive files reading
	 *
	 * @param $path           string base path
	 * @param $include_vendor boolean
	 * @return string[] an array of files names (empty directories are not included)
	 */
	private static function getFiles($path, $include_vendor = false)
	{
		$files = array();
		$dir = dir($path);
		while ($entry = $dir->read()) if (($entry[0] != ".")) {
			if (is_file("$path/$entry")) {
				$files[] = "$path/$entry";
			}
			elseif (is_dir("$path/$entry") && ($include_vendor || ($entry != "vendor"))) {
				$files = array_merge($files, static::getFiles("$path/$entry"));
			}
		}
		return $files;
	}

	//--------------------------------------------------------------------------------- getNamespaces
	/**
	 * Returns the used namespaces list for the application, including parent's applications namespaces
	 *
	 * Namespaces strings are sorted from higher-level application to basis "SAF\Framework" namespace
	 * An empty namespace will always be given first
	 *
	 * @return string[]
	 */
	public static function getNamespaces()
	{
		if (!self::$namespaces) {
			$current_configuration = Configuration::current();
			if (!$current_configuration) {
				return array(__NAMESPACE__);
			}
			else {
				$application_class = $current_configuration->getApplicationClassName();
				while (!empty($application_class) && ($application_class != 'SAF\Framework\Application')) {
					$namespace = Namespaces::of($application_class);
					self::$namespaces[] = $namespace;
					$path = Names::classToProperty(substr($namespace, strpos($namespace, "/") + 1));
					$dir = dir($path);
					while ($entry = $dir->read()) if (($entry[0] != '.') && is_dir($path . "/" . $entry)) {
						self::$namespaces[] = $namespace . "\\" . Names::propertyToClass($entry);
					}
					$dir->close();
					$application_class = get_parent_class($application_class);
				}
				self::$namespaces[] = 'SAF\Framework';
				self::$namespaces[] = 'SAF\Framework\Tests';

				self::$namespaces[] = "";
			}
		}
		return self::$namespaces;
	}

	//-------------------------------------------------------------------------- getSourceDirectories
	/**
	 * Returns the full directory list for the application, including parent's applications directory
	 *
	 * Directory names are sorted from higher-level application to basis SAF "framework" directory
	 * Inside an application, directories are sorted randomly (according to how the php Directory->read() call works)
	 *
	 * Paths are relative to the SAF index.php base script position
	 *
	 * @param $application_name string
	 * @return string[]
	 */
	public static function getSourceDirectories($application_name)
	{
		$app_dir = strtolower($application_name);
		$directories = array();
		if ($application_name != "Framework") {
			$extends = mParse(file_get_contents("{$app_dir}/Application.php"),
				" extends \\SAF\\", "\\Application"
			);
			$directories = static::getSourceDirectories($extends);
		}
		return array_merge(static::getDirectories($app_dir), $directories);
	}

	//-------------------------------------------------------------------------------- getSourceFiles
	/**
	 * Returns the full files list for the application, including parent's applications directory
	 *
	 * File names are sorted from higher-level application to basis SAF "framework" directory
	 * Inside an application, files are sorted randomly (according to how the php Directory->read() call works)
	 *
	 * Paths are relative to the SAF index.php base script position
	 *
	 * @param $application_name string
	 * @param $include_vendor   boolean
	 * @return string[]
	 */
	public static function getSourceFiles($application_name, $include_vendor = false)
	{
		$app_dir = strtolower($application_name);
		$directories = array();
		if ($application_name != "Framework") {
			$extends = mParse(file_get_contents("{$app_dir}/Application.php"),
				" extends \\SAF\\", "\\Application"
			);
			$directories = static::getSourceFiles($extends, $include_vendor);
		}
		return array_merge(static::getFiles($app_dir, $include_vendor), $directories);
	}

}
