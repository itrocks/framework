<?php
namespace SAF\Framework\View;

use SAF\Framework;
use SAF\Framework\Controller\Main;
use SAF\Framework\Logger\File_Logger;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;

/**
 * This plugin logs all view outputs sent to users.
 *
 * It simply takes the result of Controller\Main::run() and save it into a file.
 */
class Logger extends File_Logger implements Registerable
{

	//-------------------------------------------------------------------------------- FILE_EXTENSION
	const FILE_EXTENSION = 'html';

	//-------------------------------------------------------------------------------------------- GZ
	/**
	 * Override this with true if the file has to be opened using gzopen
	 */
	const GZ = true;

	//------------------------------------------------------------------------------ onMainController
	/**
	 * @param $result string
	 */
	public function onMainController($result)
	{
		if ($file = $this->file()) {
			$buffer = '#' . lParse(rLastParse($this->fileName(), SL), DOT) . LF . '<P>' . LF;
			gzputs($file, $buffer);
			gzputs($file, $result);
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
		$aop = $register->aop;
		$aop->afterMethod([Main::class, 'run'], [$this, 'onMainController']);
	}

}
