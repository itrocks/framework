<?php
namespace SAF\Framework\PHP;

/**
 * This stores a dependency between two class names
 */
class Dependency
{

	//---------------------------------------------------------------------------------- $type values
	const T_CLASS       = 'class';
	const T_DECLARATION = 'declaration';
	const T_EXTENDS     = 'extends';
	const T_IMPLEMENTS  = 'implements';
	const T_NEW         = 'new';
	const T_PARAM       = 'param';
	const T_RETURN      = 'return';
	const T_STATIC      = 'static';
	const T_USE         = 'use';
	const T_VAR         = 'var';

	//--------------------------------------------------------------------------- $declaration values
	const T_CLASS_DECLARATION     = 'class';
	const T_INTERFACE_DECLARATION = 'interface';
	const T_TRAIT_DECLARATION     = 'trait';

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * Name of the class that has a dependency
	 *
	 * @var string
	 */
	public $class_name;

	//---------------------------------------------------------------------------------- $declaration
	/**
	 * @values class, interface, trait
	 * @var string
	 */
	public $declaration;

	//------------------------------------------------------------------------------ $dependency_name
	/**
	 * Dependency class name
	 *
	 * @var string
	 */
	public $dependency_name;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * The file name where the class is stored
	 *
	 * @var string
	 */
	public $file_name;

	//----------------------------------------------------------------------------------------- $line
	/**
	 * The line in file where the dependency was parsed
	 *
	 * @var integer
	 */
	public $line;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The dependency type, matches the name of the PHP token representing the dependency type :
	 * - 'class' for a 'Dependency_Name::class' into the source code
	 * - 'extends' for a 'extends Dependency_Name' into class declaration
	 * - 'implements' for a 'implements Dependency_Name' into class declaration
	 * - 'new' for a 'new Dependency_Name' into the source code
	 * - 'param' for a '@param ...' into the source code (doc comment)
	 * - 'return' for a '@return ...' into the source code (doc comment)
	 * - 'static' for a 'Dependency_Name::' call into the source code
	 * - 'use' for a 'use Dependency_Name' into the class
	 * - 'var' for a '@var ...' into the source code (doc comment)
	 *
	 * @values class, declaration, extends, implements, new, param, return, static, use, var
	 * @var string
	 */
	public $type;

}
