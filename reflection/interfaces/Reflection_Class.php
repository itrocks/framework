<?php
namespace ITRocks\Framework\Reflection\Interfaces;

/**
 * An interface for all reflection class classes
 */
interface Reflection_Class extends Reflection
{

	//---------------------------------------------------------------------------------------- T_SORT
	const T_SORT = 'sort';

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the class
	 */
	public function __toString();

	//----------------------------------------------------------------------------------- getConstant
	/**
	 * Gets defined constant value
	 *
	 * @param $name string
	 * @return mixed
	 */
	public function getConstant(string $name);

	//---------------------------------------------------------------------------------- getConstants
	/**
	 * Gets defined constants from a class
	 *
	 * @param $flags integer[] T_EXTENDS, T_USE
	 * @return mixed[] Constant name in key, constant value in value
	 */
	public function getConstants($flags = [T_EXTENDS, T_USE]);

	//-------------------------------------------------------------------------------- getConstructor
	/**
	 * Gets the constructor of the reflected class
	 *
	 * @return Reflection_Method
	 */
	public function getConstructor();

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * Gets default value of properties
	 *
	 * @param $flags integer[] T_EXTENDS, T_USE
	 * @return array
	 */
	public function getDefaultProperties(array $flags = []);

	//----------------------------------------------------------------------------------- getFileName
	/**
	 * Gets the filename of the file in which the class has been defined
	 *
	 * @return string
	 */
	public function getFileName();

	//----------------------------------------------------------------------------- getInterfaceNames
	/**
	 * Gets the interface names
	 *
	 * @return string[]
	 */
	public function getInterfaceNames();

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * Gets interfaces
	 *
	 * @return Reflection_Class[]
	 */
	public function getInterfaces();

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Only methods visible for current class are retrieved, not the privates ones from parents or
	 * traits.
	 *
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return Reflection_Method[] key is the method name
	 * integer
	 */
	public function getMethods($flags = null);

	//------------------------------------------------------------------------------ getNamespaceName
	/**
	 * Gets namespace name
	 *
	 * @return string
	 */
	public function getNamespaceName();

	//-------------------------------------------------------------------------------- getParentClass
	/**
	 * Gets parent class
	 *
	 * @return Reflection_Class
	 */
	public function getParentClass();

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets an array of properties for the class
	 *
	 * Properties visible for current class, not the privates ones from parents and traits are
	 * retrieved but if you set T_EXTENDS and T_USE to get them.
	 * If you set self::T_SORT properties will be sorted by (@)display_order class annotation
	 *
	 * @param $flags       integer[] Restriction.
	 *                     flags @default [T_EXTENDS, T_USE] @values T_EXTENDS, T_USE, self::T_SORT
	 * @param $final_class string force the final class to this name (mostly for internal use)
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function getProperties($flags = null, $final_class = null);

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Retrieves reflected properties
	 *
	 * Only a property visible for current class can be retrieved, not the privates ones from parent
	 * classes or traits.
	 *
	 * @param $name string The name of the property to get
	 * @return Reflection_Property
	 */
	public function getProperty($name);

	//---------------------------------------------------------------------------------- getStartLine
	/**
	 * Gets starting line number
	 *
	 * @return integer
	 */
	public function getStartLine();

	//--------------------------------------------------------------------------------- getTraitNames
	/**
	 * Returns an array of names of traits used by this class
	 *
	 * @return string[]
	 */
	public function getTraitNames();

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * Gets traits
	 *
	 * @return Reflection_Class[]
	 */
	public function getTraits();

	//----------------------------------------------------------------------------------- inNamespace
	/**
	 * Checks if in namespace
	 *
	 * @return boolean
	 */
	public function inNamespace();

	//------------------------------------------------------------------------------------------- isA
	/**
	 * Returns true if the class has $name into its parents, interfaces or traits
	 *
	 * @param $name string
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return boolean
	 */
	public function isA($name, array $flags = []);

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * Checks if class is abstract
	 *
	 * @return boolean
	 */
	public function isAbstract();

	//--------------------------------------------------------------------------------------- isClass
	/**
	 * Checks if this class is a class (not an interface or a trait)
	 *
	 * @return boolean
	 */
	public function isClass();

	//--------------------------------------------------------------------------------------- isFinal
	/**
	 * Checks if class is final
	 *
	 * @return boolean
	 */
	public function isFinal();

	//------------------------------------------------------------------------------------ isInstance
	/**
	 * Checks class for instance
	 *
	 * @param $object object
	 * @return boolean
	 */
	public function isInstance(object $object);

	//----------------------------------------------------------------------------------- isInterface
	/**
	 * Checks if the class is an interface
	 *
	 * @return boolean
	 */
	public function isInterface();

	//------------------------------------------------------------------------------------ isInternal
	/**
	 * Checks if class is defined internally by an extension, or the core
	 *
	 * @return boolean
	 */
	public function isInternal();

	//--------------------------------------------------------------------------------- isUserDefined
	/**
	 * Checks if user defined
	 *
	 * @return boolean
	 */
	public function isUserDefined();

}
