<?php
namespace ITRocks\Framework\Remote_Connection;

use ITRocks\Framework\Remote_Connection;

/**
 * Local file system connection
 */
class Local implements Remote_Connection
{

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	public string $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $path string|null
	 */
	public function __construct(string $path = null)
	{
		if (isset($path)) {
			$this->path = $path;
		}
	}

	//--------------------------------------------------------------------------------------- connect
	public function connect()
	{
		// TODO: Implement connect() method.
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param $file string
	 */
	public function delete(string $file)
	{
		// TODO: Implement delete() method.
	}

	//------------------------------------------------------------------------------------------- dir
	/**
	 * @param $path string
	 */
	public function dir(string $path)
	{
		// TODO: Implement dir() method.
	}

	//------------------------------------------------------------------------------------ disconnect
	public function disconnect()
	{
		// TODO: Implement disconnect() method.
	}

	//----------------------------------------------------------------------------------------- mkdir
	/**
	 * @param $path string
	 */
	public function mkdir(string $path)
	{
		// TODO: Implement mkdir() method.
	}

	//--------------------------------------------------------------------------------------- receive
	/**
	 * @param $distant string
	 * @param $local   string
	 */
	public function receive(string $distant, string $local)
	{
		// TODO: Implement receive() method.
	}

	//------------------------------------------------------------------------------------------ send
	/**
	 * @param $local   string
	 * @param $distant string
	 */
	public function send(string $local, string $distant)
	{
		// TODO: Implement send() method.
	}

}
