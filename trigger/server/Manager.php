<?php
namespace ITRocks\Framework\Trigger\Server;

use ITRocks\Framework\Tools\OS\Asynchronous_Task;

/**
 * The trigger server manager allows to activate / deactivate the trigger server :
 *
 * - activate : install into OS scheduler (eg crontab), launch
 * - deactivate : remove from OS scheduler (eg crontab), stop
 */
class Manager
{

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		exec($this->command(), $output);
		if (join('', $output) !== 'OK') {
			trigger_error('Could not activate trigger server : ' . $output, E_USER_WARNING);
		}
		(new Asynchronous_Task($this->command()))->add();
	}

	//--------------------------------------------------------------------------------------- command
	/**
	 * @param $action string @values start, stop
	 * @return string
	 */
	protected function command(string $action = 'start') : string
	{
		return getcwd() . '/itrocks/framework/console /ITRocks/Framework/Trigger/Server/' . $action;
	}

	//------------------------------------------------------------------------------------ deactivate
	public function deactivate()
	{
		(new Asynchronous_Task($this->command()))->remove();
		exec($this->command('stop'), $output);
		if (join('', $output) !== 'OK') {
			trigger_error('Could not deactivate trigger server : ' . $output, E_USER_WARNING);
		}
	}

}
