<?php
namespace SAF\Framework\Reflection\Interfaces;

/**
 * An interface for all reflection class classes
 */
interface Reflection_Class extends Reflection
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the class
	 */
	public function __toString();

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
	public function getDefaultProperties($flags = []);

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
	public function getMethods($flags = []);

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
	 *
	 * @param $flags integer[] T_EXTENDS, T_USE
	 * @param $final_class string force the final class to this name (mostly for internal use)
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function getProperties($flags = [], $final_class = null);

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

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * Checks if class is abstract
	 *
	 * @return boolean
	 */
	public function isAbstract();

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
	 * @param $object object|string
	 * @return boolean
	 */
	public function isInstance($object);

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
