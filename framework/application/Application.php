<?php

class Application
{

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
	public static function getSourceDirectories($application_class_name)
	{
		$app_dir = strtolower($application_class_name);
		$directories = array();
		if ($application_class_name != "Framework") {
			$extends = mParse(file_get_contents("{$app_dir}/{$application_class_name}_Application.php"),
				" extends ", "_Application"
			);
			$directories = Application::getSourceDirectories($extends);
		}
		return array_merge(Application::getDirectories($app_dir), $directories);
	}

}
