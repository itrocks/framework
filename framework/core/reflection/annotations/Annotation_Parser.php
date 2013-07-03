<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/annotations/Annotation.php";
require_once "framework/core/reflection/annotations/templates/Multiple_Annotation.php";

/**
 * The annotation parser process calculates the annotation value
 */
abstract class Annotation_Parser
{

	//---------------------------------------------------------------------------------------- byName
	/**
	 * Parse a given annotation from a reflection class / method / property / etc. doc comment
	 *
	 * @param $reflection_object Has_Doc_Comment
	 * @param $annotation_name   string
	 * @param $multiple          boolean if null, multiple automatically set if annotation class is a Multiple_Annotation
	 * @return Annotation|Annotation[]
	 */
	public static function byName(
		Has_Doc_Comment $reflection_object, $annotation_name, $multiple = null
	) {
		$annotation_class = static::getAnnotationClassName(
			($reflection_object instanceof Reflection_Class)
			? "Class_" . Names::propertyToClass($annotation_name)
			: $annotation_name
		);
		if (!isset($multiple)) {
			$multiple = is_a($annotation_class, 'SAF\Framework\Multiple_Annotation', true);
		}
		$doc_comment = $reflection_object->getDocComment(true);
		$annotations = array();
		$annotation = null;
		$i = 0;
		while (($i = strpos($doc_comment, "@" . $annotation_name, $i)) !== false) {
			$annotation = self::parseAnnotationValue(
				$doc_comment, $annotation_name, $i, $annotation_class, $reflection_object
			);
			if (isset($annotation)) {
				if ($multiple) {
					$annotations[] = $annotation;
				}
				else {
					break;
				}
			}
		}
		$annotation = $multiple ? $annotations : (
			$annotation ? $annotation : new $annotation_class(null, $reflection_object)
		);
		return $annotation;
	}

	//-------------------------------------------------------------------------------- allAnnotations
	/**
	 * Parses all annotations of a reflection object
	 *
	 * @param $reflection_object Has_Doc_Comment
	 * @return Annotation[]|array
	 */
	public static function allAnnotations(Has_Doc_Comment $reflection_object)
	{
		$doc_comment = $reflection_object->getDocComment(true);
		$annotations = array();
		$i = 0;
		while (($i = strpos($doc_comment, "@", $i)) !== false) {
			$j = strlen($doc_comment);
			if (($k = strpos($doc_comment, "\n", $i)) < $j) $j = $k;
			if (($k = strpos($doc_comment, " ", $i)) < $j)  $j = $k;
			$annotation_name = substr($doc_comment, $i + 1, $j - $i - 1);
			$annotation_class = static::getAnnotationClassName(
				($reflection_object instanceof Reflection_Class)
					? "Class_" . Names::propertyToClass($annotation_name)
					: $annotation_name
			);
			$multiple = is_a($annotation_class, 'SAF\Framework\Multiple_Annotation', true);
			$annotation = self::parseAnnotationValue(
				$doc_comment, $annotation_name, $i, $annotation_class, $reflection_object
			);
			if (isset($annotation)) {
				if ($multiple) {
					$annotations[$annotation_name][] = $annotation;
				}
				else {
					$annotations[$annotation_name] = $annotation;
				}
			}
		}
		return $annotations;
	}

	//------------------------------------------------------------------------ getAnnotationClassName
	/**
	 * Gets annotation class name (including namespace) for a given annotation name
	 *
	 * @param $annotation_name string
	 * @return string
	 */
	private static function getAnnotationClassName($annotation_name)
	{
		static $annotations_classes = array();
		if (isset($annotations_classes[$annotation_name])) {
			$annotation_class = $annotations_classes[$annotation_name];
		}
		else {
			$annotation_class = Namespaces::fullClassName(
				Names::propertyToClass($annotation_name) . "_Annotation"
			);
			if (!class_exists($annotation_class)) {
				$annotation_class = 'SAF\Framework\Annotation';
			}
			$annotations_classes[$annotation_name] = $annotation_class;
		}
		return $annotation_class;
	}

	//-------------------------------------------------------------------------- parseAnnotationValue
	/**
	 * @param $doc_comment       string
	 * @param $annotation_name   string
	 * @param $i                 integer
	 * @param $annotation_class  string
	 * @param $reflection_object Has_Doc_Comment
	 * @return Annotation
	 */
	private static function parseAnnotationValue(
		$doc_comment, $annotation_name, &$i, $annotation_class, Has_Doc_Comment $reflection_object
	) {
		$i += strlen($annotation_name) + 1;
		$next_char = $doc_comment[$i];
		switch ($next_char) {
			case " ": case "\t":
				$i ++;
				$j = strpos($doc_comment, "\n", $i);
				$value = trim(substr($doc_comment, $i, $j - $i));
				break;
			case "\r": case "\n":
				$value = true;
				break;
			default:
				$value = null;
		}
		return isset($value) ? new $annotation_class($value, $reflection_object) : null;
	}

}
