<?php
namespace ITRocks\Framework\RAD\Feature;

/**
 * Feature status
 */
abstract class Status
{

	//------------------------------------------------------------------------------------- AVAILABLE
	/**
	 * The feature is available, but not yet installed
	 */
	const AVAILABLE = 'available';

	//-------------------------------------------------------------------------------------- BUILT_IN
	/**
	 * The feature is built-in into the software : you cannot install / uninstall it
	 */
	const BUILT_IN = 'built-in';

	//------------------------------------------------------------------------------------- INSTALLED
	/**
	 * The feature is installed
	 */
	const INSTALLED = 'installed';

}
