<?php
namespace ITRocks\Framework\Reflection\Interfaces;

use ITRocks\Framework\Reflection\Reflection_Attribute;
use ReflectionException;

/**
 * An interface for all reflection classes
 */
interface Reflection_Class extends Reflection
{

	//---------------------------------------------------------------------------------------- T_SORT
	const T_SORT = 'sort';

	//------------------------------------------------------------------------------------ __toString
	/** @return string The name of the class */
	public function __toString() : string;

	//--------------------------------------------------------------------------------- getAttributes
	/** @return Reflection_Attribute[] */
	public function getAttributes(?string $name, int $flags = 0,
		Reflection|Reflection_Class $final = null, Reflection_Class $class = null
	) : array;

	//----------------------------------------------------------------------------------- getConstant
	/** Gets defined constant value */
	public function getConstant(string $name) : mixed;

	//---------------------------------------------------------------------------------- getConstants
	/**
	 * Gets defined constants from a class
	 *
	 * @param $flags integer[] @values T_EXTENDS, T_USE
	 * @return array Constant name in key, constant value in value
	 * @todo migrate to int, for php 8.0 hard typing full compatibility
	 */
	public function getConstants(array|int $flags = [T_EXTENDS, T_USE]) : array;

	//-------------------------------------------------------------------------------- getConstructor
	/** Gets the constructor of the reflected class */
	public function getConstructor() : ?Reflection_Method;

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * Gets default value of properties
	 *
	 * @param $flags integer[] @values T_EXTENDS, T_USE
	 * @return array
	 */
	public function getDefaultProperties(array $flags = []) : array;

	//----------------------------------------------------------------------------------- getFileName
	/** Gets the filename of the file in which the class has been defined */
	public function getFileName() : string|false;

	//----------------------------------------------------------------------------- getInterfaceNames
	/** @return string[] */
	public function getInterfaceNames() : array;

	//--------------------------------------------------------------------------------- getInterfaces
	/** @return Reflection_Class[] */
	public function getInterfaces() : array;

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * Gets an array of methods for the class
	 *
	 * Only methods visible for current class are retrieved, not the privates ones from parents or
	 * traits.
	 *
	 * @param $flags integer[]|null @values T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return Reflection_Method[] key is the method name
	 */
	public function getMethods(array|int $flags = null) : array;

	//------------------------------------------------------------------------------ getNamespaceName
	public function getNamespaceName() : string;

	//-------------------------------------------------------------------------------- getParentClass
	public function getParentClass() : ?Reflection_Class;

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * Gets an array of properties for the class
	 *
	 * Properties visible for current class, not the privates ones from parents and traits are
	 * retrieved but if you set T_EXTENDS and T_USE to get them.
	 * If you set self::T_SORT properties will be sorted by (@)display_order class annotation
	 *
	 * @param $flags       integer[]|null Restriction.
	 *                     flags @default [T_EXTENDS, T_USE] @values T_EXTENDS, T_USE, self::T_SORT
	 * @return Reflection_Property[] key is the name of the property
	 */
	public function getProperties(array|int $flags = null) : array;

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * Retrieves reflected properties
	 *
	 * Only a property visible for current class can be retrieved, not the privates ones from parent
	 * classes or traits.
	 *
	 * @param $name string The name of the property to get
	 * @return ?Reflection_Property
	 * @throws ReflectionException
	 */
	public function getProperty(string $name) : ?Reflection_Property;

	//---------------------------------------------------------------------------------- getStartLine
	public function getStartLine() : int|false;

	//--------------------------------------------------------------------------------- getTraitNames
	/** @return string[] */
	public function getTraitNames() : array;

	//------------------------------------------------------------------------------------- getTraits
	/** @return Reflection_Class[] */
	public function getTraits() : array;

	//----------------------------------------------------------------------------------- inNamespace
	public function inNamespace() : bool;

	//------------------------------------------------------------------------------------------- isA
	/**
	 * Returns true if the class has $name into its parents, interfaces or traits
	 *
	 * @param $name string
	 * @param $flags integer[] @values T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return boolean
	 */
	public function isA(string $name, array $flags = []) : bool;

	//------------------------------------------------------------------------------------ isAbstract
	public function isAbstract() : bool;

	//--------------------------------------------------------------------------------------- isClass
	/** Checks if this class is a class (not an interface or a trait) */
	public function isClass() : bool;

	//--------------------------------------------------------------------------------------- isFinal
	public function isFinal() : bool;

	//------------------------------------------------------------------------------------ isInstance
	public function isInstance(object $object) : bool;

	//----------------------------------------------------------------------------------- isInterface
	public function isInterface() : bool;

	//------------------------------------------------------------------------------------ isInternal
	/** Checks if class is defined internally by an extension, or the core */
	public function isInternal() : bool;

	//--------------------------------------------------------------------------------- isUserDefined
	/** Checks if user defined */
	public function isUserDefined() : bool;

	//-------------------------------------------------------------------------------------------- of
	public static function of(string $class_name) : static;

}
