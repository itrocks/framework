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
	public static function getSourceDirectories($app_name)
	{
		$directories = array();
		if ($app_name != "Framework") {
			$extends = mParse(file_get_contents("{$app_name}/{$app_name}_Application.php"),
				" extends ", "_Application"
			);
			$directories = Application::getSourceDirectories($extends);
		}
		return array_merge(Application::getDirectories($app_name), $directories);
	}

}
