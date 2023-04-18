<?php
namespace ITRocks\Framework\PHP\Dependency;

/**
 * Dependency declaration constants
 */
abstract class Declaration
{

	//---------------------------------------------------- Dependency::$declaration for $type = class
	public const _CLASS     = 'class';
	public const _INTERFACE = 'interface';
	public const _TRAIT     = 'trait';

	//-------------------------------------------------- Dependency::$declaration for $type = feature
	public const ASSIGNED    = 'assigned';
	public const BUILT_IN    = 'built-in';
	public const INSTALLABLE = 'installable';

	//-------------------------------------------------------------------------------------- PROPERTY
	/** Dependency::$declaration for $type in (param, return, var) */
	public const PROPERTY  = 'property';

}
