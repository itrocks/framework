<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Application;

/**
 * Class Additional_Annotation support additional annotations added by plugins
 *
 * @private
 */
abstract class Additional_Annotations
{

	//----------------------------------------------------------------------- $additional_annotations
	/**
	 * @var string[]
	 */
	private static array $additional_annotations = [];

	//----------------------------------------------------------------- $shutdown_function_registered
	/**
	 * @var boolean
	 */
	private static bool $shutdown_function_registered = false;

	//------------------------------------------------------------------- enableAdditionalAnnotations
	/**
	 * Called only when a plugin has added annotation on registration
	 *
	 * Saves a cached default_annotations.php file with standard and additional annotations
	 */
	public static function enableAdditionalAnnotations() : void
	{
		$cached_annotations_file = Application::getCacheDir() . SL . 'default_annotations.php';
		if (self::$additional_annotations) {
			$default_annotations_file = __DIR__ . '/../reflection/annotation'
				. SL . 'default_annotations.php';
			$buffer = file_get_contents($default_annotations_file)
				. LF
				. 'Parser::$default_annotations = array_merge(' . LF
				. TAB . 'Parser::$default_annotations,' . LF
				. TAB . 'unserialize(' . Q . serialize(self::$additional_annotations) . Q . ')' . LF
				. ');' . LF;
			$cache = file_exists($cached_annotations_file)
				? file_get_contents($cached_annotations_file)
				: '';
			if ($buffer !== $cache) {
				script_put_contents($cached_annotations_file, $buffer);
			}
		}
		else {
			clearstatcache();
			if (file_exists($cached_annotations_file)) {
				unlink($cached_annotations_file);
			}
		}
	}

	//---------------------------------------------------------------------- registerShutdownFunction
	/**
	 * Register a shutdown function to enable additional annotations save-to-cache
	 */
	private static function registerShutdownFunction() : void
	{
		if (!self::$shutdown_function_registered) {
			register_shutdown_function([self::class, 'enableAdditionalAnnotations']);
			self::$shutdown_function_registered = true;
		}
	}

	//--------------------------------------------------------------------------------- setAnnotation
	/**
	 * Defines an annotation class, linked to an annotation
	 *
	 * @param $context          string Parser::T_CLASS, Parser::T_METHOD, Parser::T_PROPERTY
	 * @param $annotation_name  string
	 * @param $annotation_class string
	 */
	public static function setAnnotation(
		string $context, string $annotation_name, string $annotation_class
	) : void
	{
		// register the shutdown function
		self::registerShutdownFunction();
		// add annotation
		self::$additional_annotations[$context . '@' . $annotation_name] = $annotation_class;
	}

	//-------------------------------------------------------------------------------- setAnnotations
	/**
	 * Defines multiple annotations classes
	 * A very bit faster than multiple calls to setAnnotation()
	 *
	 * @param $context             string Parser::T_CLASS, Parser::T_METHOD, Parser::T_PROPERTY
	 * @param $annotations_classes string[] key is the annotation name, value is the annotation class
	 */
	public static function setAnnotations(string $context, array $annotations_classes) : void
	{
		// register the shutdown function
		self::registerShutdownFunction();
		// add annotations
		foreach ($annotations_classes as $annotation_name => $annotation_class) {
			self::$additional_annotations[$context . '@' . $annotation_name] = $annotation_class;
		}
	}

}
