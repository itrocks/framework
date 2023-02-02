<?php
namespace ITRocks\Framework\Dao\Mysql\File_Logger;

use ITRocks\Framework;
use ITRocks\Framework\Dao\Mysql\File_Logger;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Store;
use ITRocks\Framework\Session;

/**
 * Logger entry trait to view output into ITRocks\Framework\Logger\Entry
 */
trait Entry
{

	//------------------------------------------------------------------------------------------ $sql
	/**
	 * @max_length 1000000000
	 * @multiline
	 */
	#[Getter('getSql')]
	#[Store(false)]
	public string $sql;

	//---------------------------------------------------------------------------------------- getSql
	/**
	 * @noinspection PhpUnused #Getter
	 */
	protected function getSql() : string
	{
		/** @var $logger File_Logger */
		$logger = Session::current()->plugins->get(File_Logger::class);
		/** @var $this Framework\Logger\Entry|Entry */
		return $logger ? $logger->readFileContent($this) : '';
	}

}
