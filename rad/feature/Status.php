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

	//------------------------------------------------------------------------------------- INSTALLED
	/**
	 * The feature is installed
	 */
	const INSTALLED = 'installed';

}
