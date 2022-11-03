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
	public function delete(string $file);

	//------------------------------------------------------------------------------------------- dir
	/**
	 * @param $path string
	 */
	public function dir(string $path);

	//------------------------------------------------------------------------------------ disconnect
	public function disconnect();

	//----------------------------------------------------------------------------------------- mkdir
	/**
	 * @param $path string
	 */
	public function mkdir(string $path);

	//--------------------------------------------------------------------------------------- receive
	/**
	 * @param $distant string
	 * @param $local   string
	 */
	public function receive(string $distant, string $local);

	//------------------------------------------------------------------------------------------ send
	/**
	 * @param $local   string
	 * @param $distant string
	 */
	public function send(string $local, string $distant);

}
