<?php
namespace SAF\Framework\Unit_Tests;

/**
 * A runnable unit test
 */
interface Runnable_Unit_Test
{

	//----------------------------------------------------------------------------------------- begin
	public function begin();

	//------------------------------------------------------------------------------------------- end
	public function end();

	//------------------------------------------------------------------------------------------- run
	public function run();

}
