<?php
namespace ITRocks\Framework\View\Logger;

use ITRocks\Framework;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\No_Escape;
use ITRocks\Framework\View\Logger;

/**
 * Logger entry trait to view output into ITRocks\Framework\Logger\Entry
 */
trait Entry
{

	//--------------------------------------------------------------------------------------- $output
	/**
	 * @getter
	 * @max_length 100000000
	 * @multiline
	 * @store false
	 * @user_getter userGetOutput
	 * @var string
	 */
	public $output;

	//----------------------------------------------------------------------------- deactivateScripts
	/**
	 * @param $output string
	 * @return string
	 */
	private function deactivateScripts($output)
	{
		return str_ireplace(
			[
				'<script', '</script>',
				'<link',   '</link>',
				'<head',   '</head>'
			],
			[
				'&lt;script',    '&lt/script>',
				'&lt;link',      '&lt/link&gt;',
				'<pre>&lt;head', '&lt;/head></pre>'
			],
			$output
		);
	}

	//------------------------------------------------------------------------------------- getOutput
	/**
	 * @return string
	 */
	private function getOutput()
	{
		/** @var $logger Logger */
		$logger = Session::current()->plugins->get(Logger::class);
		/** @var $this Framework\Logger\Entry|Entry */
		return $logger ? $logger->readFileContent($this) : '';
	}

	//--------------------------------------------------------------------------------- userGetOutput
	/**
	 * @return string
	 */
	public function userGetOutput()
	{
		return new No_Escape($this->deactivateScripts($this->getOutput()), No_Escape::TYPE_STRING);
	}

}
