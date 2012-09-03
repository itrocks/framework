<?php
namespace Framework;

class Application
{

	//----------------------------------------------------------------------------------- $namespaces
	private static $namespaces;

	//-------------------------------------------------------------------------------- getDirectories
	private static function getDirectories($path)
	{
		$directories = array($path);
		$dir = dir($path);
		while ($entry = $dir->read()) {
			if (is_dir("$path/$entry") && $entry[0] != ".") {
				$directories = array_merge($directories, Application::getDirectories("$path/$entry"));
			}
		}
		return $directories;
	}

	//-------------------------------------------------------------------------- getSourceDirectories
	public static function getSourceDirectories($application_name)
	{
		$app_dir = strtolower($application_name);
		$directories = array();
		if ($application_name != "Framework") {
			$extends = mParse(file_get_contents("{$app_dir}/{$application_name}_Application.php"),
				" extends ", "_Application"
			);
			$directories = Application::getSourceDirectories($extends);
		}
		return array_merge(Application::getDirectories($app_dir), $directories);
	}

	//--------------------------------------------------------------------------------- getNamespaces
	public static function getNamespaces($application_name)
	{
		if (!Application::$namespaces) {
			$app_dir = strtolower($application_name);
			Application::$namespaces = array(); 
			$application = $application_name;
			while ($application != "Framework") {
				Application::$namespaces[] = $application;
				$application =  mParse(file_get_contents("{$app_dir}/{$application_name}_Application.php"),
					" extends ", "_Application"
				);
			}
			Application::$namespaces[] = "Framework";
		}
		return Application::$namespaces;
	}

}
