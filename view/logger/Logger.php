<?php
namespace ITRocks\Framework\View;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Logger\File_Logger;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Call_Stack;

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
	 * @param $result ?string
	 */
	public function onMainController(?string $result) : void
	{
		if ((new Call_Stack)->methodCount([Main::class, 'run']) > 1) {
			return;
		}
		if ($file = $this->file()) {
			$buffer = '#' . lParse(rLastParse($this->fileName(), SL), DOT) . LF . '<P>' . LF;
			gzputs($file, $buffer);
			gzputs($file, strval($result));
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod([Main::class, 'run'], [$this, 'onMainController']);
	}

}
