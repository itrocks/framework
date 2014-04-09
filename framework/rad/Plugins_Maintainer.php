<?php
namespace SAF\Framework\RAD;

use SAF\Framework;

/**
 * Maintains the plugins database :
 * - bidirectional synchronization with plugin.php files
 */
class Plugins_Maintainer
{

	//------------------------------------------------------------------------------- filesToDatabase
	public function filesToDatabase()
	{
		foreach (
			Framework\Application::current()->include_path->getSourceFiles() as $file_path
		) {
			if (pathinfo($file_path)['file_name'] == 'plugin.php') {
				/** @noinspection PhpIncludeInspection */
				$plugin_configuration = (include $file_path);
				echo '<pre>' . print_r($plugin_configuration, true) . '</pre>';
			}
		}
	}

	//------------------------------------------------------------------------------- databaseToFiles
	public function databaseToFiles()
	{

	}

}
