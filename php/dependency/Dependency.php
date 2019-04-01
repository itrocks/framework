<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\PHP\Dependency\Tools;

/**
 * This stores a dependency between two class names
 *
 * @business
 * @index set type, dependency_name
 */
class Dependency
{
	use Tools;

	//---------------------------------------------------------------------------------- $type values
	const T_BRIDGE_FEATURE = 'bridge_feature';
	const T_CLASS          = 'class';
	const T_COMPATIBILITY  = 'compatibility';
	const T_DECLARATION    = 'declaration';
	const T_EXTENDS        = 'extends';
	const T_FEATURE        = 'feature';
	const T_IMPLEMENTS     = 'implements';
	const T_NAMESPACE_USE  = 'namespace_use';
	const T_NEW            = 'new';
	const T_PARAM          = 'param';
	const T_RETURN         = 'return';
	const T_SET            = 'set';
	const T_STATIC         = 'static';
	const T_STORE          = 'store';
	const T_USE            = 'use'; // class' use <trait>
	const T_VAR            = 'var';

	//--------------------------------------------------------------------------- $declaration values
	const T_CLASS_DECLARATION     = 'class';
	const T_INTERFACE_DECLARATION = 'interface';
	const T_PROPERTY_DECLARATION  = 'property';
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
	 * @values class, interface, property, trait
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
	 * - 'store' for a '@set ...' into the source code (lowercase storage repository name)
	 * - 'use' for a 'use Dependency_Name' into the class
	 * - 'var' for a '@var ...' into the source code (property doc comment)
	 *
	 * @values bridge_feature, class, compatibility, declaration, extends, feature, implements,
	 *         namespace_use, new, param, return, set, static, store, use, var
	 * @var string
	 */
	public $type;

}
