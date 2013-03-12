<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/annotations/Annotation.php";
require_once "framework/core/reflection/annotations/templates/Multiple_Annotation.php";

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
			$multiple = is_subclass_of($annotation_class, 'SAF\Framework\Multiple_Annotation');
		}
		$doc_comment = $reflection_object->getDocComment(true);
		$annotations = array();
		$annotation = null;
		$i = 0;
		while (($i = strpos($doc_comment, "@" . $annotation_name, $i)) !== false) {
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
			if (isset($value)) {
				$annotation = new $annotation_class($value, $reflection_object);
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

	//------------------------------------------------------------------------ getAnnotationClassName
	/**
	 * Gets annotation class name (including namespace) for a given annotation name
	 *
	 * @param $annotation_name string
	 * @return string
	 */
	public static function getAnnotationClassName($annotation_name)
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

}
