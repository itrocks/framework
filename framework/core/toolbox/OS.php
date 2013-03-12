<?php
namespace SAF\Framework;

abstract class OS
{

	//------------------------------------------------------------------------------ includeSeparator
	/**
	 * The include separator is ":" under unix/linux and ";" under windows systems
	 *
	 * @return string
	 */
	public static function includeSeparator()
	{
		return (PHP_OS === "WINNT") ? ";" : ":";
	}

}
