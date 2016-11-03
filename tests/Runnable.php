<?php
namespace ITRocks\Framework\Tests;

/**
 * A runnable unit test
 */
interface Runnable
{

	//----------------------------------------------------------------------------------------- begin
	public function begin();

	//------------------------------------------------------------------------------------------- end
	public function end();

	//------------------------------------------------------------------------------------------- run
	public function run();

}
