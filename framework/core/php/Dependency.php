<?php
namespace SAF\PHP;

/**
 * This stores a dependency between two class names
 */
class Dependency
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * Name of the class that has a dependency
	 *
	 * @var string
	 */
	public $class_name;

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
	 * - T_CLASS for a 'Dependency_Name::class' into the source code
	 * - T_EXTENDS for a 'extends Dependency_Name' into class declaration
	 * - T_IMPLEMENTS for a 'implements Dependency_Name' into class declaration
	 * - T_NEW for a 'new Dependency_Name' into the source code
	 * - T_STATIC for a 'Dependency_Name::' call into the source code
	 * - T_USE for a 'use Dependency_Name' into the class
	 *
	 * @values T_CLASS, T_EXTENDS, T_IMPLEMENTS, T_NEW, T_STATIC, T_USE
	 * @var integer
	 */
	public $type;

}
