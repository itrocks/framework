<?php
namespace SAF\Framework;

require_once "framework/classes/Configuration.php";
require_once "framework/classes/toolbox/String.php";

abstract class Application
{

	//----------------------------------------------------------------------------------- $namespaces
	/**
	 * Namespaces list cache : initialized at first use
	 *
	 * @var multitype:string
	 */
	protected static $namespaces;

	//-------------------------------------------------------------------------------- getDirectories
	/**
	 * This is called by getSourceDirectories() for recursive directories reading.
	 * 
	 * @param string $path base path
	 * @return multitype:string an array of directories names
	 */
	private static function getDirectories($path)
	{
		$directories = array($path);
		$dir = dir($path);
		while ($entry = $dir->read()) {
			if (is_dir("$path/$entry") && ($entry[0] != ".")) {
				$directories = array_merge($directories, static::getDirectories("$path/$entry"));
			}
		}
		return $directories;
	}

	//-------------------------------------------------------------------------- getSourceDirectories
	/**
	 * Returns the full directory list for the application, including parent's applications directory.
	 *
	 * Directory names are sorted from higher-level application to basis SAF "framework" directory.
	 * Inside an application, directories are sorted randomly (according to how the php Directory->read() call works).
	 *
	 * Paths are relative to the SAF index.php base script position.
	 *
	 * @param string $application_name
	 * @return multitype:string
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

	//--------------------------------------------------------------------------------- getNamespaces
	/**
	 * Returns the used namespaces list for the application, including parent's applications namespaces.
	 *
	 * Namespaces strings are sorted from higher-level application to basis "SAF\Framework" namespace.
	 * An empty namespace will always be given first.
	 *
	 * @return multitype:string
	 */
	public static function getNamespaces()
	{
		if (!self::$namespaces) {
			$current_configuration = Configuration::current();
			if (!$current_configuration) {
				return array(__NAMESPACE__);
			} else {
				$application = $current_configuration->getApplicationName();
				$app_path = strtolower($application);
				self::$namespaces = array(""); 
				while ($application != "Framework") {
					self::$namespaces[] = "SAF\\" . $application;
					$application =  mParse(file_get_contents("$app_path/Application.php"),
						" extends \\SAF\\", "\\Application"
					);
					$app_path = strtolower(
						is_dir(strtolower($application)) ? $application : "_" . $application
					);
				}
				self::$namespaces[] = __NAMESPACE__;
				// TODO should found another way to make it smarter (prehaps framework_test application ?)
				self::$namespaces[] = __NAMESPACE__ . "\\Tests";
			}
		}
		return self::$namespaces;
	}

}
