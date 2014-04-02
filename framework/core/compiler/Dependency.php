<?php
namespace SAF\Framework;

/**
 * This stores a dependency between two classes
 */
class Dependency
{

	//----------------------------------------------------------------------------------------- const
	const T_CLASS      = 'class';
	const T_EXTENDS    = 'extends';
	const T_IMPLEMENTS = 'implements';
	const T_NEW        = 'new';
	const T_STATIC     = 'static';
	const T_USES       = 'uses';

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//------------------------------------------------------------------------------ $dependency_name
	/**
	 * @var string
	 */
	public $dependency_name;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	public $file_name;

	//----------------------------------------------------------------------------------------- $line
	/**
	 * @var integer
	 */
	public $line;

	//---------------------------------------------------------------------------------------- $types
	/**
	 * @values class, extends, implements, new, static, uses
	 * @var string
	 */
	public $type;

}
