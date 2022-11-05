<?php
namespace ITRocks\Framework;

/**
 * A remote connection interface
 */
interface Remote_Connection
{

	//--------------------------------------------------------------------------------------- connect
	public function connect();

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param $file string
	 */
	public function delete(string $file) : void;

	//------------------------------------------------------------------------------------------- dir
	/**
	 * @param $path string
	 */
	public function dir(string $path) : void;

	//------------------------------------------------------------------------------------ disconnect
	public function disconnect() : void;

	//----------------------------------------------------------------------------------------- mkdir
	/**
	 * @param $path string
	 */
	public function mkdir(string $path) : void;

	//--------------------------------------------------------------------------------------- receive
	/**
	 * @param $distant string
	 * @param $local   string
	 */
	public function receive(string $distant, string $local) : void;

	//------------------------------------------------------------------------------------------ send
	/**
	 * @param $local   string
	 * @param $distant string
	 */
	public function send(string $local, string $distant) : void;

}
