<?php
namespace SAF\Framework;

class Ssh_Connection implements Remote_Connection
{

	//----------------------------------------------------------------------------------------- $host
	/**
	 * @var string
	 */
	public $host;

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public $login;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @var string
	 */
	public $password;

	//--------------------------------------------------------------------------------------- connect
	public function connect()
	{
		// TODO: Implement connect() method.
	}

	//---------------------------------------------------------------------------------------- delete
	public function delete($file)
	{
		// TODO: Implement delete() method.
	}

	//------------------------------------------------------------------------------------------- dir
	public function dir($path)
	{
		// TODO: Implement dir() method.
	}

	//------------------------------------------------------------------------------------ disconnect
	public function disconnect()
	{
		// TODO: Implement disconnect() method.
	}

	//----------------------------------------------------------------------------------------- mkdir
	public function mkdir($path)
	{
		// TODO: Implement mkdir() method.
	}

	//--------------------------------------------------------------------------------------- receive
	public function receive($distant, $local)
	{
		// TODO: Implement receive() method.
	}

	//------------------------------------------------------------------------------------------ send
	public function send($local, $distant)
	{
		// TODO: Implement send() method.
	}
}
