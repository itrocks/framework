<?php
namespace ITRocks\Framework\Error_Handler;

// phpcs:ignoreFile -- code ordered constants
/**
 * Error handler priorities constants
 */
abstract class Error_Handler_Priority
{

	//--------------------------------------------------------------------------------------- HIGHEST
	const HIGHEST = 0;

	//------------------------------------------------------------------------------------------ HIGH
	const HIGH = 1;

	//---------------------------------------------------------------------------------------- NORMAL
	const NORMAL = 2;

	//------------------------------------------------------------------------------------------- LOW
	const LOW = 3;

	//---------------------------------------------------------------------------------------- LOWEST
	const LOWEST = 4;

}
