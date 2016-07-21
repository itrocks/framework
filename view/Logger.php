<?php
namespace SAF\Framework\View;

use SAF\Framework;
use SAF\Framework\Controller\Main;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Session;

/**
 * This plugin logs all view outputs sent to users.
 *
 * It simply takes the result of Controller\Main::run() and save it into a file.
 */
class Logger implements Configurable, Registerable
{

	//------------------------------------------------------------------------------------------ PATH
	const PATH = 'path';

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	private $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration) && isset($configuration[self::PATH])) {
			$this->path = $configuration[self::PATH];
		}
	}

	//------------------------------------------------------------------------------------------ file
	/**
	 * @return resource
	 */
	private function file()
	{
		static $file = null;
		if (empty($file) && ($filename = $this->fileName())) {
			if (!file_exists($path = lLastParse($filename, SL))) {
				mkdir($path, 0777, true);
			}
			$file = gzopen($filename, 'w9');
		}
		return $file;
	}

	//-------------------------------------------------------------------------------------- fileName
	/**
	 * @return string
	 */
	private function fileName()
	{
		static $file_name = null;
		if (empty($file_name)) {
			/** @var $logger Framework\Logger */
			$logger = Session::current()->plugins->get(Framework\Logger::class);
			if ($identifier = $logger->getIdentifier()) {
				$path = $this->path . SL . date('Y-m-d');
				$file_name = $path . SL . $identifier . '.html.gz';
			}
		}

		return $file_name;
	}

	//------------------------------------------------------------------------------ onMainController
	/**
	 * @param $uri    string
	 * @param $get    array
	 * @param $post   array
	 * @param $files  array
	 * @param $result string
	 */
	public function onMainController($uri, $get, $post, $files, $result)
	{
		if ($file = $this->file()) {
			$buffer = lParse(rLastParse($this->fileName(), SL), DOT) . LF;
			$buffer .= PRE . LF;
			$buffer .= 'uri = ' . $uri . LF;
			if ($get)   $buffer .= 'get = ' . print_r($get, true) . LF;
			if ($post)  $buffer .= 'post = ' . print_r($post, true) . LF;
			if ($files) $buffer .= 'files = ' . print_r($files, true) . LF;
			$buffer .= _PRE . LF . '<hr>' . LF . LF;
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
