<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Application;
use ITRocks\Framework\Autoloader;
use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Class_\Display;
use ITRocks\Framework\Reflection\Attribute\Class_\Displays;
use ITRocks\Framework\Reflection\Reflection_Class;
use ReflectionClass;

/**
 * A library of feature to transform PHP elements names
 */
abstract class Names
{

	//------------------------------------------------------------------------------------ $irregular
	/**
	 * These nouns and adjectives are invariant and should not be changed from/to singular / plural
	 */
	public static array $irregular = [
		// invariant nouns @see https://en.wiktionary.org/wiki/Category:English_invariant_nouns
		'aircraft', 'bass', 'radar', 'sheep',
		// invariant adjectives : they are all invariant
		'next', 'previous'
	];

	//----------------------------------------------------------------------------------------- $sets
	/**
	 * @var string[] key is the name of the set class, value is the matching name of the single class
	 */
	private static array $sets = [];

	//------------------------------------------------------------------------------ classToDirectory
	/**
	 * Changes 'A\Namespace\Class_Name' into 'class_name'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToDirectory(string $class_name) : string
	{
		return strtolower(Namespaces::shortClassName($class_name));
	}

	//-------------------------------------------------------------------------------- classToDisplay
	/**
	 * Changes 'A\Namespace\Class_Name' into 'class name'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $annotation boolean if true, will use the value of #Display first, if exists
	 * @return string
	 */
	public static function classToDisplay(string $class_name, bool $annotation = true) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection Should be called with valid class name */
		$display = ($annotation && class_exists($class_name))
			? Display::of(new Reflection_Class($class_name))->value
			: null;
		return $display ?: strtolower(str_replace('_', SP, Namespaces::shortClassName($class_name)));
	}

	//------------------------------------------------------------------------------- classToDisplays
	/**
	 * Changes 'A\Namespace\Class_Name' (or set class name) into 'class names'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $annotation boolean if true, will use the value of #Displays first, if exists
	 * @return string
	 */
	public static function classToDisplays(string $class_name, bool $annotation = true) : string
	{
		if (!class_exists($class_name)) {
			$class_name = static::setToClass($class_name);
		}
		/** @noinspection PhpUnhandledExceptionInspection Should be called with valid class name(s) */
		$displays = (class_exists($class_name) && $annotation)
			? Displays::of(new Reflection_Class($class_name))->value
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
	public static function classToFilePath(string $class_name) : string
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
	 * @param $prefix     string
	 * @return string
	 */
	public static function classToMethod(string $class_name, string $prefix = '') : string
	{
		$method_name = str_replace('_', '', Namespaces::shortClassName($class_name));
		return $prefix ? ($prefix . $method_name) : lcfirst($method_name);
	}

	//----------------------------------------------------------------------------------- classToPath
	/**
	 * Changes 'A\Class\Name\Like\This' into 'a/class/name/like/This'
	 *
	 * TODO check usages and see if replacement by classToFilePath will work
	 * TODO Does not work in Engine::getTemplateFile() can't find Application_home.html
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToPath(string $class_name) : string
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
	public static function classToProperty(string $class_name) : string
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
	public static function classToSet(string $class_name) : string
	{
		/** @noinspection PhpUnhandledExceptionInspection Must be called with a valid class name */
		return Class_\Set::of(new Reflection_Class($class_name))->value;
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
	public static function classToUri(object|string $class_name) : string
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
		while (str_starts_with($class_name, $built_path)) {
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
	public static function displayToClass(string $display) : string
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
	public static function displayToDirectory(string $display) : string
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
	public static function displayToProperty(string $display) : string
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
	public static function fileToClass(string $file_name) : string
	{
		$class_name = self::pathToClass(lParse($file_name, DOT));
		if (!(
			class_exists($class_name)
			|| trait_exists($class_name)
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
	public static function fileToDisplay(string $file_name) : string
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
	public static function methodToClass(string $method_name) : string
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
	public static function methodToDisplay(string $method_name) : string
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
	public static function methodToProperty(string $method_name) : string
	{
		$property_name = strtolower(preg_replace('%([a-z])([A-Z])%', '$1_$2', $method_name));
		if (str_starts_with($property_name, 'get_') || str_starts_with($property_name, 'set_')) {
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
	public static function pathToClass(string $class_name) : string
	{
		return str_replace(SL, BS, ucfirst(preg_replace_callback(
			'%[_/][a-z]%',
			function(array $matches) : string { return strtoupper($matches[0]); },
			$class_name
		)));
	}

	//--------------------------------------------------------------------------- propertyPathToField
	/**
	 * Changes 'a.name.and.sub_name' into 'a[name][and][sub_name]'
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyPathToField(string $property_name) : string
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
	public static function propertyToClass(string $property_name) : string
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
	public static function propertyToDisplay(string $property_name) : string
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
	public static function propertyToMethod(string $property_name, string $prefix = '') : string
	{
		$method = '';
		$name = explode('_', $property_name);
		foreach ($name as $value) {
			$method .= ucfirst($value);
		}
		return $prefix ? ($prefix . $method) : lcfirst($method);
	}

	//------------------------------------------------------------------------------------ setToClass
	/**
	 * Changes 'A\Namespace\Class_Names' into 'A\Namespace\Class_Name'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name  string
	 * @param $check_class boolean false if you don't want to check for existing classes
	 * @return string
	 */
	public static function setToClass(string $class_name, bool $check_class = true) : string
	{
		if (isset(self::$sets[$class_name])) {
			return self::$sets[$class_name];
		}
		// if $class_name is explicitly declared as 'singular' : return it
		if (Dependency::hasSet($class_name)) {
			self::$sets[$class_name] = $class_name;
			return $class_name;
		}
		// explicitly declared as 'plural of ...' : return the matching class name
		if ($dependency_class_name = Dependency::dependencyToClass($class_name)) {
			self::$sets[$class_name] = $dependency_class_name;
			return $dependency_class_name;
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
				/** @noinspection PhpUnhandledExceptionInspection Reflection_Class : class exists */
				if (
					(class_exists($set_class_name) || trait_exists($set_class_name))
					&& (Class_\Set::of(new Reflection_Class($set_class_name))->value === $set_class_name)
				) {
					self::$sets[$set_class_name] = $set_class_name;
					return $set_class_name;
				}
				elseif ($check_class && error_reporting()) {
					trigger_error('No class found for set ' . $set_class_name, E_USER_ERROR);
				}
				else {
					$right      = $class_name . $right;
					$class_name = '';
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
	public static function setToSingle(string $set) : string
	{
		if ($found = array_search($set, static::$irregular)) {
			return is_numeric($found) ? $set : $found;
		}
		if (!str_ends_with($set, 'ss')) {
			if     (str_ends_with($set, 'ies'))   return substr($set, 0, -3) . 'y';
			elseif (str_ends_with($set, 'ses'))   return substr($set, 0, -2);
			elseif (str_ends_with($set, 'ches'))  return substr($set, 0, -2);
			elseif (str_ends_with($set, 's'))     return substr($set, 0, -1);
			elseif (str_ends_with($set, 'men'))   return substr($set, 0, -3) . 'man';
		}
		return $set;
	}

	//----------------------------------------------------------------------------------- singleToSet
	/**
	 * @example 'value' -> 'values'
	 * @param $single string
	 * @return string
	 */
	public static function singleToSet(string $single) : string
	{
		if (isset(static::$irregular[$single])) {
			return static::$irregular[$single];
		}
		if (array_search($single, static::$irregular)) {
			return $single;
		}
		return
			str_ends_with($single, 'ay')  ? ($single . 's') : (
			str_ends_with($single, 'y')   ? (substr($single, 0, -1) . 'ies') : (
			str_ends_with($single, 'man') ? (substr($single, 0, -3) . 'men') : (
			str_ends_with($single, 'ss')  ? ($single . 'es') : (
			str_ends_with($single, 's')   ? $single : (
				$single . 's'
			)))));
	}

	//------------------------------------------------------------------------------------ uriToClass
	/**
	 * @param $uri string
	 * @return string
	 */
	public static function uriToClass(string $uri) : string
	{
		while (!ctype_upper(substr(rLastParse($uri, SL), 0, 1))) {
			$uri = lLastParse($uri, SL);
		}
		return Names::setToSingle(str_replace(SL, BS, substr($uri, 1)));
	}

}
