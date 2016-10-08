<?php
namespace SAF\Framework\PHP;

/**
 * This stores a dependency between two class names
 *
 * @business
 * @index set type, dependency_name
 */
class Dependency
{

	//---------------------------------------------------------------------------------- $type values

	//--------------------------------------------------------------------------------------- T_CLASS
	const T_CLASS = 'class';

	//--------------------------------------------------------------------------------- T_DECLARATION
	const T_DECLARATION = 'declaration';

	//------------------------------------------------------------------------------------- T_EXTENDS
	const T_EXTENDS = 'extends';

	//---------------------------------------------------------------------------------- T_IMPLEMENTS
	const T_IMPLEMENTS = 'implements';

	//----------------------------------------------------------------------------------------- T_NEW
	const T_NEW = 'new';

	//--------------------------------------------------------------------------------------- T_PARAM
	const T_PARAM = 'param';

	//-------------------------------------------------------------------------------------- T_RETURN
	const T_RETURN = 'return';

	//----------------------------------------------------------------------------------------- T_SET
	const T_SET = 'set';

	//-------------------------------------------------------------------------------------- T_STATIC
	const T_STATIC = 'static';

	//----------------------------------------------------------------------------------------- T_USE
	const T_USE = 'use';

	//----------------------------------------------------------------------------------------- T_VAR
	const T_VAR = 'var';

	//--------------------------------------------------------------------------- $declaration values

	//--------------------------------------------------------------------------- T_CLASS_DECLARATION
	const T_CLASS_DECLARATION = 'class';

	//----------------------------------------------------------------------- T_INTERFACE_DECLARATION
	const T_INTERFACE_DECLARATION = 'interface';

	//--------------------------------------------------------------------------- T_TRAIT_DECLARATION
	const T_TRAIT_DECLARATION = 'trait';

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
	 * - 'class' for a '*::class' into the source code
	 * - 'extends' for a 'extends Dependency_Name' into class declaration
	 * - 'implements' for a 'implements Dependency_Name' into class declaration
	 * - 'new' for a 'new Dependency_Name' into the source code
	 * - 'param' for a '@param ...' into the source code (method doc comment)
	 * - 'return' for a '@return ...' into the source code (method doc comment)
	 * - 'set' for a '@set ...' into the source code (class doc comment)
	 * - 'static' for a '__CLASS_NAME__::' / 'self::' / 'static::' / 'Dependency_Name::' call
	 * - 'use' for a 'use Dependency_Name' into the class
	 * - 'var' for a '@var ...' into the source code (property doc comment)
	 *
	 * @values class, declaration, extends, implements, new, param, return, set, static, use, var
	 * @var string
	 */
	public $type;

}
