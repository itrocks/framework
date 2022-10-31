<?php
namespace ITRocks\Framework\Dao\Mysql;

/**
 * A Mysql process, as shown by SHOW [FULL] PROCESSLIST
 */
class Process
{

	//-------------------------------------------------------------------------------------- $Command
	/**
	 * @var string
	 */
	protected string $Command;

	//----------------------------------------------------------------------------------------- $Host
	/**
	 * @var string
	 */
	protected string $Host;

	//------------------------------------------------------------------------------------------- $Id
	/**
	 * @var integer
	 */
	protected int $Id;

	//----------------------------------------------------------------------------------------- $Info
	/**
	 * @var string
	 */
	protected string $Info;

	//---------------------------------------------------------------------------------------- $State
	/**
	 * @var string
	 */
	protected string $State;

	//----------------------------------------------------------------------------------------- $Time
	/**
	 * @var integer
	 */
	protected int $Time;

	//----------------------------------------------------------------------------------------- $User
	/**
	 * @var string
	 */
	protected string $User;

	//------------------------------------------------------------------------------------------- $db
	/**
	 * @var string
	 */
	protected string $db;

	//------------------------------------------------------------------------------ getMysqlThreadId
	/**
	 * @return integer
	 */
	public function getMysqlThreadId() : int
	{
		return $this->Id;
	}

}
