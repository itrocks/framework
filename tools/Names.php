<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Application;
use ITRocks\Framework\Autoloader;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Class_\Display_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Displays_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Set_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ReflectionClass;

/**
 * A library of feature to transform PHP elements names
 */
abstract class Names
{

	//------------------------------------------------------------------------------------ $irregular
	/**
	 * This nouns and adjectives are invariant and should not be changed from/to singular / plural
	 */
	public static $irregular = [
		// invariant nouns @see https://en.wiktionary.org/wiki/Category:English_invariant_nouns
		'aircraft', 'bass', 'radar', 'sheep',
		// invariant adjectives : they are all invariant
		'next', 'previous'
	];

	//----------------------------------------------------------------------------------------- $sets
	/**
	 * @var string[] key is the name of the set class, value is the matching name of the single class
	 */
	private static $sets = [];

	//------------------------------------------------------------------------------ classToDirectory
	/**
	 * Changes 'A\Namespace\Class_Name' into 'class_name'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToDirectory($class_name)
	{
		return strtolower(Namespaces::shortClassName($class_name));
	}

	//-------------------------------------------------------------------------------- classToDisplay
	/**
	 * Changes 'A\Namespace\Class_Name' into 'class name'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $annotation boolean if true, will use the value of @display first, if exists
	 * @return string
	 */
	public static function classToDisplay($class_name, $annotation = true)
	{
		/** @noinspection PhpUnhandledExceptionInspection Should be called with valid class name */
		$display = ($annotation && class_exists($class_name))
			? Display_Annotation::of(new Reflection_Class($class_name))->value
			: null;
		return $display ?: strtolower(str_replace('_', SP, Namespaces::shortClassName($class_name)));
	}

	//------------------------------------------------------------------------------- classToDisplays
	/**
	 * Changes 'A\Namespace\Class_Name' (or set class name) into 'class names'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return string
	 */
	public static function classToDisplays($class_name)
	{
		if (!class_exists($class_name)) {
			$class_name = static::setToClass($class_name);
		}
		/** @noinspection PhpUnhandledExceptionInspection Should be called with valid class name(s) */
		$displays = class_exists($class_name)
			? Displays_Annotation::of(new Reflection_Class($class_name))->value
			: null;
		return $displays
			?: strtolower(
				str_replace('_', SP, Namespaces::shortClassName(static::classToSet($class_name)))
			);
	}

	//------------------------------------------------------------------------------- classToFilePath
	/**
	 * Changes 'A\Namespace\Class_Name' into 'a/namespace/Class_Name.php' or
	 * 'a/namespace/class_name/Class_Name.php'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return string
	 */
	public static function classToFilePath($class_name)
	{
		$file_path = Autoloader::getFilePath($class_name);
		if (!$file_path) {
			$error_reporting = error_reporting(E_ALL & ~E_DEPRECATED);
			/** @noinspection PhpUnhandledExceptionInspection Source code must be valid, or it crashes */
			$class = new ReflectionClass($class_name);
			error_reporting($error_reporting);
			$file_path = Paths::getRelativeFileName($class->getFileName());
		}
		return $file_path;
	}

	//--------------------------------------------------------------------------------- classToMethod
	/**
	 * Changes 'A\Namespace\Class_Name' into 'className'
	 *
	 * @param $class_name string
	 * @param $prefix string
	 * @return string
	 */
	public static function classToMethod($class_name, $prefix = null)
	{
		$method_name = str_replace('_', '', Namespaces::shortClassName($class_name));
		return $prefix ? $prefix . $method_name : lcfirst($method_name);
	}

	//----------------------------------------------------------------------------------- classToPath
	/**
	 * Changes 'A\Class\Name\Like\This' into 'a/class/name/like/This'
	 *
	 * TODO check usages and see if replacement by classToFilePath will work
	 * TODO Does not work in Engine::getTemplateFile() can't find Application_home.html
	 *
	 * @deprecated : now will use classToFilePath()
	 * @param $class_name string
	 * @return string
	 */
	public static function classToPath($class_name)
	{
		$i = strrpos($class_name, BS);
		return str_replace(BS, SL, strtolower(substr($class_name, 0, $i)) . substr($class_name, $i));
	}

	//------------------------------------------------------------------------------- classToProperty
	/**
	 * Changes 'A\Namespace\Class_Name' into 'class_name'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToProperty($class_name)
	{
		return strtolower(Namespaces::shortClassName($class_name));
	}

	//------------------------------------------------------------------------------------ classToSet
	/**
	 * Changes 'A\Namespace\Class_Name' into 'A\Namespace\Class_Names'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return string
	 */
	public static function classToSet($class_name)
	{
		/** @noinspection PhpUnhandledExceptionInspection Must be called with a valid class name */
		return Set_Annotation::of(new Reflection_Class($class_name))->value;
	}

	//------------------------------------------------------------------------------------ classToUri
	/**
	 * Gets the URI of a class name or object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @example class name User : 'ITRocks/Framework/User'
	 * @example User object of id = 1 : 'ITRocks/Framework/User/1'
	 * @param $class_name object|string
	 * @return string
	 */
	public static function classToUri($class_name)
	{
		// get object id, if object
		if (is_object($class_name)) {
			$id         = Dao::getObjectIdentifier($class_name, 'id');
			$class_name = get_class($class_name);
		}
		// link classes : get linked class
		/** @noinspection PhpUnhandledExceptionInspection Must be called with a valid class name */
		while (Link_Annotation::of(new Reflection_Class($class_name))->value) {
			$class_name = get_parent_class($class_name);
		}
		// built classes : get object class
		$built_path = Application::current()->getNamespace() . BS . 'Built' . BS;
		while (substr($class_name, 0, strlen($built_path)) == $built_path) {
			$class_name = get_parent_class($class_name);
		}
		// replace \ by /
		return str_replace(BS, SL, $class_name) . (isset($id) ? (SL . $id) : '');
	}

	//-------------------------------------------------------------------------------- displayToClass
	/**
	 * Changes 'a text' do a valid normalized directory name (without spaces nor special characters)
	 *
	 * @param $display string
	 * @return string
	 */
	public static function displayToClass($display)
	{
		return str_replace(SP, '_', ucwords(str_replace('_', SP, $display)));
	}

	//---------------------------------------------------------------------------- displayToDirectory
	/**
	 * Changes 'a text' do a valid normalized directory name (without spaces nor special characters)
	 *
	 * @param $display string
	 * @return string
	 */
	public static function displayToDirectory($display)
	{
		return strtolower(str_replace(SP, '_', $display));
	}

	//----------------------------------------------------------------------------- displayToProperty
	/**
	 * Changes 'a text' into 'a_text'
	 *
	 * @param $display string
	 * @return string
	 */
	public static function displayToProperty($display)
	{
		return strtolower(str_replace(SP, '_', $display));
	}

	//----------------------------------------------------------------------------------- fileToClass
	/**
	 * Changes a 'full/path/Class_File_Name.php' into 'Full\Path\Class_File_Name'
	 *
	 * This checks if the class exist and gets the correct case of it
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $file_name string
	 * @return string
	 */
	public static function fileToClass($file_name)
	{
		$class_name = self::pathToClass(lParse($file_name, DOT));
		if (!(
			class_exists($class_name)
			|| trait_exists($class_name, true)
			|| interface_exists($class_name)
		)) {
			$class_name = lLastParse($class_name, BS);
		}
		/** @noinspection PhpUnhandledExceptionInspection Class name must be valid */
		return (new Reflection_Class($class_name))->name;
	}

	//--------------------------------------------------------------------------------- fileToDisplay
	/**
	 * Changes a 'full/path/file_name.ext' into 'file name'
	 *
	 * @param $file_name string
	 * @return string
	 */
	public static function fileToDisplay($file_name)
	{
		if (($i = strpos($file_name, SL)) !== false) {
			$file_name = substr($file_name, $i + 1);
		}
		if (($i = strpos($file_name, DOT)) !== false) {
			$file_name = substr($file_name, 0, $i);
		}
		return str_replace('_', SP, $file_name);
	}

	//--------------------------------------------------------------------------------- methodToClass
	/**
	 * Changes 'aMethodName' into 'A_Method_Name'
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function methodToClass($method_name)
	{
		return ucfirst(preg_replace('%([a-z|0-9])([A-Z])%', '$1_$2', $method_name));
	}

	//------------------------------------------------------------------------------- methodToDisplay
	/**
	 * Changes 'aMethodName' into 'a method name'
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function methodToDisplay($method_name)
	{
		return strtolower(preg_replace('%([a-z])([A-Z])%', '$1 $2', $method_name));
	}

	//------------------------------------------------------------------------------ methodToProperty
	/**
	 * Changes 'aMethodName' into 'a_method_name'
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function methodToProperty($method_name)
	{
		$property_name = strtolower(preg_replace('%([a-z])([A-Z])%', '$1_$2', $method_name));
		if ((substr($property_name, 0, 4) == 'get_') || (substr($property_name, 0, 4) == 'set_')) {
			$property_name = substr($property_name, 4);
		}
		return $property_name;
	}

	//----------------------------------------------------------------------------------- pathToClass
	/**
	 * Changes 'a/class/name/like/This' into 'A\Class\Name\Like\This'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function pathToClass($class_name)
	{
		return str_replace(SL, BS, ucfirst(preg_replace_callback(
			'%[_/][a-z]%', function($matches) { return strtoupper($matches[0]); }, $class_name
		)));
	}

	//--------------------------------------------------------------------------- propertyPathToField
	/**
	 * Changes 'a.name.and.sub_name' into 'a[name][and][sub_name]'
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyPathToField($property_name)
	{
		if ($i = strpos($property_name, DOT)) {
			$property_name = substr($property_name, 0, $i)
				. '[' . str_replace(DOT, '][', substr($property_name, $i + 1)) . ']';
		}
		return $property_name;
	}

	//------------------------------------------------------------------------------- propertyToClass
	/**
	 * Changes 'a_property_name' into 'A_Property_Name'
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyToClass($property_name)
	{
		return str_replace(SP, '_', ucwords(str_replace('_', SP, $property_name)));
	}

	//----------------------------------------------------------------------------- propertyToDisplay
	/**
	 * Changes 'a_property_name' into 'a property name'
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyToDisplay($property_name)
	{
		return str_replace('_', SP, $property_name);
	}

	//------------------------------------------------------------------------------ propertyToMethod
	/**
	 * Changes 'a_property_name' into 'aPropertyName'
	 *
	 * @param $property_name string
	 * @param $prefix string
	 * @return string
	 */
	public static function propertyToMethod($property_name, $prefix = null)
	{
		$method = '';
		$name = explode('_', $property_name);
		foreach ($name as $value) {
			$method .= ucfirst($value);
		}
		return $prefix ? $prefix . $method : lcfirst($method);
	}

	//------------------------------------------------------------------------------------ setToClass
	/**
	 * Changes 'A\Namespace\Class_Names' into 'A\Namespace\Class_Name'
	 *
	 * @param $class_name  string
	 * @param $check_class boolean false if you don't want to check for existing classes
	 * @return string
	 */
	public static function setToClass($class_name, $check_class = true)
	{
		if (isset(self::$sets[$class_name])) {
			return self::$sets[$class_name];
		}
		// if $class_name is explicitely declared as 'singular' : return it
		$dependency = Dao::searchOne(
			['class_name' => Func::equal($class_name), 'type' => Dependency::T_SET],
			Dependency::class
		);
		if ($dependency) {
			self::$sets[$class_name] = $class_name;
			return $class_name;
		}
		// explicitely declared as 'plural of ...' : return the matching class name
		$dependency = Dao::searchOne(
			['dependency_name' => Func::equal($class_name), 'type' => Dependency::T_SET],
			Dependency::class
		);
		if ($dependency) {
			self::$sets[$class_name] = $dependency->class_name;
			return $dependency->class_name;
		}
		// guess the singular using common syntactic changes (apply setToSingle to words)
		$set_class_name = $class_name;
		$class_name     = Namespaces::shortClassName($class_name);
		$right          = '';
		do {
			$class_name      = self::setToSingle($class_name);
			$full_class_name = Namespaces::defaultFullClassName($class_name . $right, $set_class_name);
			if (class_exists($full_class_name) || trait_exists($full_class_name)) {
				self::$sets[$set_class_name] = $full_class_name;
				return $full_class_name;
			}
			$i = strrpos($class_name, '_');
			if (strrpos($class_name, BS) > $i) {
				$i = false;
			}
			if ($i === false) {
				if (
					/** @noinspection PhpUnhandledExceptionInspection Reflection_Class : class exists */
					(class_exists($set_class_name) || trait_exists($set_class_name))
					&& (Set_Annotation::of(new Reflection_Class($set_class_name))->value === $set_class_name)
				) {
					self::$sets[$set_class_name] = $set_class_name;
					return $set_class_name;
				}
				elseif ($check_class && error_reporting()) {
					trigger_error('No class found for set ' . $set_class_name, E_USER_ERROR);
				}
				else {
					$right      = substr($class_name, $i) . $right;
					$class_name = substr($class_name, 0, $i);
				}
			}
			else {
				$right      = substr($class_name, $i) . $right;
				$class_name = substr($class_name, 0, $i);
			}
		}
		while (!empty($class_name));
		$class_name .= $right;
		if (class_exists($class_name, false) || trait_exists($class_name, false)) {
			self::$sets[$set_class_name] = $class_name;
			return $class_name;
		}
		elseif (strrpos($set_class_name, '_') > strrpos($set_class_name, BS)) {
			$namespace  = Namespaces::of($set_class_name);
			$class_name = substr($set_class_name, strpos($set_class_name, '_', strlen($namespace)) + 1);
			self::$sets[$set_class_name] = self::setToClass($namespace . BS . $class_name, $check_class);
			return self::$sets[$set_class_name];
		}
		else {
			self::$sets[$set_class_name] = $set_class_name;
			return $set_class_name;
		}
	}

	//----------------------------------------------------------------------------------- setToSingle
	/**
	 * @example 'values' -> 'value'
	 * @param $set string
	 * @return string
	 */
	public static function setToSingle($set)
	{
		if ($found = array_search($set, static::$irregular)) {
			return is_numeric($found) ? $set : $found;
		}
		if (substr($set, -2) !== 'ss') {
			if     (substr($set, -3) === 'ies')   return substr($set, 0, -3) . 'y';
			elseif (substr($set, -3) === 'ses')   return substr($set, 0, -2);
			elseif (substr($set, -4) === 'ches')  return substr($set, 0, -2);
			elseif (substr($set, -1) === 's')     return substr($set, 0, -1);
			elseif (substr($set, -3) === 'men')   return substr($set, 0, -3) . 'man';
		}
		return $set;
	}

	//----------------------------------------------------------------------------------- singleToSet
	/**
	 * @example 'value' -> 'values'
	 * @param $single string
	 * @return string
	 */
	public static function singleToSet($single)
	{
		if (isset(static::$irregular[$single])) {
			return static::$irregular[$single];
		}
		if (array_search($single, static::$irregular)) {
			return $single;
		}
		return
			(substr($single, -2) === 'ay')  ? ($single . 's') : (
			(substr($single, -1) === 'y')   ? (substr($single, 0, -1) . 'ies') : (
			(substr($single, -3) === 'man') ? (substr($single, 0, -3) . 'men') : (
			(substr($single, -2) === 'ss')  ? ($single . 'es') : (
			(substr($single, -1) === 's')   ? $single : (
				$single . 's'
			)))));
	}

	//------------------------------------------------------------------------------------ uriToClass
	/**
	 * @param $uri string
	 * @return string
	 */
	public static function uriToClass($uri)
	{
		while (!ctype_upper(substr(rLastParse($uri, SL), 0, 1))) {
			$uri = lLastParse($uri, SL);
		}
		return Names::setToSingle(str_replace(SL, BS, substr($uri, 1)));
	}

}
