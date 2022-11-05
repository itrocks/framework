<?php
namespace ITRocks\Framework\Remote_Connection;

use ITRocks\Framework\Remote_Connection;

/**
 * Ftp connection
 */
class Ftp implements Remote_Connection
{

	//----------------------------------------------------------------------------------------- $host
	/**
	 * @var string
	 */
	public string $host;

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public string $login;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @var string
	 */
	public string $password;

	//--------------------------------------------------------------------------------------- connect
	public function connect() : void
	{
		// TODO: Implement connect() method.
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param $file string
	 */
	public function delete(string $file) : void
	{
		// TODO: Implement delete() method.
	}

	//------------------------------------------------------------------------------------------- dir
	/**
	 * @param $path string
	 */
	public function dir(string $path) : void
	{
		// TODO: Implement dir() method.
	}

	//------------------------------------------------------------------------------------ disconnect
	public function disconnect() : void
	{
		// TODO: Implement disconnect() method.
	}

	//----------------------------------------------------------------------------------------- mkdir
	/**
	 * @param $path string
	 */
	public function mkdir(string $path) : void
	{
		// TODO: Implement mkdir() method.
	}

	//--------------------------------------------------------------------------------------- receive
	/**
	 * @param $distant string
	 * @param $local   string
	 */
	public function receive(string $distant, string $local) : void
	{
		// TODO: Implement receive() method.
	}

	//------------------------------------------------------------------------------------------ send
	/**
	 * @param $local   string
	 * @param $distant string
	 */
	public function send(string $local, string $distant) : void
	{
		// TODO: Implement send() method.
	}

}
