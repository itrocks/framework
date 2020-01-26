<?php
namespace ITRocks\Framework\PHP\Dependency;

/**
 * Dependency declaration constants
 */
abstract class Declaration
{

	//---------------------------------------------------- Dependency::$declaration for $type = class
	const _CLASS     = 'class';
	const _INTERFACE = 'interface';
	const _TRAIT     = 'trait';

	//-------------------------------------------------- Dependency::$declaration for $type = feature
	const ASSIGNED    = 'assigned';
	const BUILT_IN    = 'built-in';
	const INSTALLABLE = 'installable';

	//-------------------------------------------------------------------------------------- PROPERTY
	/**
	 * Dependency::$declaration for $type in (param, return, var)
	 */
	const PROPERTY  = 'property';

}
