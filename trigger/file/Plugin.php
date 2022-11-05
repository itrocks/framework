<?php
namespace ITRocks\Framework\Trigger\File;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger\Action\Status;
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
	public int $rate = 1000;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration string|string[]
	 */
	public function __construct(mixed $configuration = null)
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

	//--------------------------------------------------------------------------------- multipleFiles
	/**
	 * @param $file  File
	 * @param $files string[]
	 */
	protected function multipleFiles(File $file, array $files) : void
	{
		$file_path = $file->file_path;
		foreach ($files as $file_path) {
			$file->file_path = $file_path;
			$this->singleFile($file);
		}
		$file->file_path = $file_path;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$register->aop->beforeMethod([Server::class, 'loop'], [$this, 'watchFiles']);
	}

	//------------------------------------------------------------------------------------ singleFile
	/**
	 * Single file trigger
	 *
	 * @param $file File
	 */
	protected function singleFile(File $file) : void
	{
		if ($file->delete_flag_file) {
			unlink($file->file_path);
		}
		elseif (!$file->trigger_static) {
			if (!State::get($file)) {
				return;
			}
			State::set($file);
			State::purge();
		}
		$this->triggerActions($file);
	}

	//-------------------------------------------------------------------------------- triggerActions
	/**
	 * @param $file File
	 */
	protected function triggerActions(File $file) : void
	{
		$date = Date_Time::now();
		if ($file->delete_flag_file) {
			foreach ($file->actions as $action) {
				$action->next = $date;
				Dao::write($action, Dao::only('next'));
			}
		}
		else {
			foreach ($file->actions as $action) {
				$search = [
					'parent' => $action,
					'status' => Func::in(Status::RUNNING_STATUSES)
				];
				if (!Dao::searchOne($search, Action::class)) {
					$action->next = $date;
					Dao::write($action, Dao::only('next'));
				}
			}
		}
	}

	//------------------------------------------------------------------------------------ watchFiles
	public function watchFiles() : void
	{
		clearstatcache();
		foreach (Dao::readAll(File::class) as $file) {
			if (!str_contains($file->file_path, '*')) {
				if (file_exists($file->file_path)) {
					$this->singleFile($file);
				}
			}
			elseif ($files = glob($file->file_path)) {
				$this->multipleFiles($file, $files);
			}
		}
	}

}
