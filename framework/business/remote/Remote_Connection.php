<?php
namespace SAF\Framework;

/**
 * A remote connection interface
 */
interface Remote_Connection
{

	//--------------------------------------------------------------------------------------- connect
	/**
	 */
	public function connect();

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param $file string
	 */
	public function delete($file);

	//------------------------------------------------------------------------------------------- dir
	/**
	 * @param $path string
	 */
	public function dir($path);

	//------------------------------------------------------------------------------------ disconnect
	/**
	 */
	public function disconnect();

	//----------------------------------------------------------------------------------------- mkdir
	/**
	 * @param $path string
	 */
	public function mkdir($path);

	//--------------------------------------------------------------------------------------- receive
	/**
	 * @param $distant string
	 * @param $local   string
	 */
	public function receive($distant, $local);

	//------------------------------------------------------------------------------------------ send
	/**
	 * @param $local   string
	 * @param $distant string
	 */
	public function send($local, $distant);

}
