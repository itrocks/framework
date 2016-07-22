<?php
namespace SAF\Framework\View\Logger;

use SAF\Framework;
use SAF\Framework\Session;
use SAF\Framework\View\Logger;

/**
 * Logger entry trait to view output into SAF\Framework\Logger\Entry
 */
trait Entry
{

	//--------------------------------------------------------------------------------------- $output
	/**
	 * @getter
	 * @max_length 100000000
	 * @multiline
	 * @store false
	 * @var string
	 */
	public $output;

	//------------------------------------------------------------------------------------- getOutput
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * @return string
	 */
	private function getOutput()
	{
		/** @var $logger Logger */
		$logger = Session::current()->plugins->get(Logger::class);
		/** @var $this Framework\Logger\Entry|Entry */
		return $logger->readFileContent($this);
	}

}
