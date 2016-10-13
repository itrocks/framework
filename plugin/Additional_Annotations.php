<?php
namespace SAF\Framework\Plugin;

use SAF\Framework\Application;
use SAF\Framework\Tools\Names;

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
	private static $additional_annotations = [];

	//----------------------------------------------------------------- $shutdown_function_registered
	/**
	 * @var boolean
	 */
	private static $shutdown_function_registered = false;

	//------------------------------------------------------------------- enableAdditionalAnnotations
	/**
	 * Called only when a plugin has added annotation on registration
	 */
	static public function enableAdditionalAnnotations()
	{
		$cached_annotations_file
			= Application::current()->getCacheDir() . SL . 'default_annotations.php';
		if (self::$additional_annotations) {
			$buffer = file_get_contents(__DIR__ . SL . 'default_annotations.php')
				. LF
				. 'Parser::$default_annotations = array_merge(' . LF
				. TAB . 'Parser::$default_annotations,' . LF
				. TAB . 'unserialize(' . Q . serialize(self::$additional_annotations) . Q . ')' . LF
				. ');' . LF;
			$cache = file_get_contents($cached_annotations_file);
			if ($buffer !== $cache) {
				file_put_contents($cached_annotations_file, $buffer);
			}
		}
		else {
			clearstatcache();
			if (file_exists($cached_annotations_file)) {
				unlink($cached_annotations_file);
			}
		}
	}

	//---------------------------------------------------------------------- registerShutDownFunction
	/**
	 * Register a shutdown function to enable additional annotations
	 */
	private static function registerShutdownFunction()
	{
		if (!self::$shutdown_function_registered) {
			register_shutdown_function([self::class, 'enableAdditionalAnnotations']);
			self::$shutdown_function_registered = true;
		}
	}

	//----------------------------------------------------------------------- setAdditionalAnnotation
	/**
	 * Defines an annotation class, linked to an annotation
	 *
	 * @param $context          string Parser::T_CLASS, Parser::T_METHOD, Parser::T_PROPERTY
	 * @param $annotation_name  string
	 * @param $annotation_class string
	 */
	public static function addAnnotation($context, $annotation_name, $annotation_class)
	{
		// register the shutdown function
		self::registerShutdownFunction();
		// add annotation
		$namespace = 'SAF\Framework\Reflection\Annotation' . BS . $context;
		$class_name = Names::propertyToClass($annotation_name) . '_Annotation';
		self::$additional_annotations[$namespace . BS . $class_name] = $annotation_class;
	}

	//---------------------------------------------------------------------- setAdditionalAnnotations
	/**
	 * Defines multiple annotations classes
	 * A very little bit faster than multiple calls to setAnnotation()
	 *
	 * @param $context             string Parser::T_CLASS, Parser::T_METHOD, Parser::T_VARIABLE
	 * @param $annotations_classes string[] key is the annotation name, value is the annotation class
	 */
	public static function addAnnotations($context, $annotations_classes)
	{
		// register the shutdown function
		self::registerShutdownFunction();
		// add annotations
		$namespace = 'SAF\Framework\Reflection\Annotation' . BS . $context;
		foreach ($annotations_classes as $annotation_name => $annotation_class) {
			$class_name = Names::propertyToClass($annotation_name) . '_Annotation';
			self::$additional_annotations[$namespace . BS . $class_name] = $annotation_class;
		}
	}

}
