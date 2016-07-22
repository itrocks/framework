<?php
namespace SAF\Framework\Dao\Mysql\File_Logger;

use SAF\Framework;
use SAF\Framework\Session;
use SAF\Framework\Dao\Mysql\File_Logger;

/**
 * Logger entry trait to view output into SAF\Framework\Logger\Entry
 */
trait Entry
{

	//------------------------------------------------------------------------------------------ $sql
	/**
	 * @getter
	 * @max_length 1000000000
	 * @multiline
	 * @store false
	 * @var string
	 */
	public $sql;

	//---------------------------------------------------------------------------------------- getSql
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * @return string
	 */
	private function getSql()
	{
		/** @var $logger File_Logger */
		$logger = Session::current()->plugins->get(File_Logger::class);
		/** @var $this Framework\Logger\Entry|Entry */
		return $logger->readFileContent($this);
	}

}
