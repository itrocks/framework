<?php
namespace ITRocks\Framework\Trigger\File;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger\File;
use ITRocks\Framework\Trigger\Server;

/**
 * The file trigger plugin
 *
 * This watches if flag files are coming
 */
class Plugin implements Configurable, Registerable
{

	//----------------------------------------------------------------------------------------- $rate
	/**
	 * Watch rate in ms
	 *
	 * @var integer
	 */
	public $rate = 1000;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string|string[]
	 */
	public function __construct($configuration = null)
	{
		if (!$configuration) {
			return;
		}
		if (!is_array($configuration)) {
			$configuration = ['rate' => $configuration];
		}
		foreach ($configuration as $property_name => $value) {
			$this->$property_name = $value;
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->beforeMethod([Server::class, 'loop'], [$this, 'watchFiles']);
	}

	//------------------------------------------------------------------------------------ watchFiles
	public function watchFiles()
	{
		clearstatcache();
		foreach (Dao::readAll(File::class) as $file) {
			if (file_exists($file->file_path)) {
				unlink($file->file_path);
				foreach ($file->actions as $action) {
					$action->next = Date_Time::now();
					Dao::write($action, Dao::only('next'));
				}
			}
		}
	}

}
