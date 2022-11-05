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
