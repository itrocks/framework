<?php
namespace ITRocks\Framework\Tools;

/**
 * This offers OS specific detection and features
 */
abstract class OS
{

	//---------------------------------------------------------------------------- $include_separator
	/**
	 * The include separator is ':' under unix/linux and ';' under windows systems
	 *
	 * @var string
	 */
	public static $include_separator;

}

OS::$include_separator = (PHP_OS === 'WINNT') ? ';' : ':';
