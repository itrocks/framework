<?php
namespace ITRocks\Framework\Dao\Mysql\File_Logger;

use ITRocks\Framework;
use ITRocks\Framework\Dao\Mysql\File_Logger;
use ITRocks\Framework\Session;

/**
 * Logger entry trait to view output into ITRocks\Framework\Logger\Entry
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
	public string $sql;

	//---------------------------------------------------------------------------------------- getSql
	/**
	 * @noinspection PhpUnused @getter
	 * @return string
	 */
	protected function getSql() : string
	{
		/** @var $logger File_Logger */
		$logger = Session::current()->plugins->get(File_Logger::class);
		/** @var $this Framework\Logger\Entry|Entry */
		return $logger ? $logger->readFileContent($this) : '';
	}

}
