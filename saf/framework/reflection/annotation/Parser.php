<?php
namespace SAF\Framework\Reflection\Annotation;

use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use SAF\Framework\Reflection\Has_Doc_Comment;
use SAF\Framework\Tools\Names;

/**
 * The annotation parser process calculates the annotation value
 */
abstract class Parser
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
		$annotation_class = static::getAnnotationClassName($reflection_object, $annotation_name);
		if (!isset($multiple)) {
			$multiple = is_a($annotation_class, Multiple_Annotation::class, true);
		}
		$doc_comment = $reflection_object->getDocComment(true);
		$annotations = [];
		$annotation = null;
		$i = 0;
		while (($i = strpos($doc_comment, '* @' . $annotation_name, $i)) !== false) {
			$i += 2;
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
		$annotations = [];
		$i = 0;
		while (($i = strpos($doc_comment, '* @', $i)) !== false) {
			$i += 2;
			$j = strlen($doc_comment);
			if (($k = strpos($doc_comment, LF, $i)) < $j) $j = $k;
			if (($k = strpos($doc_comment, SP, $i)) < $j)  $j = $k;
			$annotation_name = substr($doc_comment, $i + 1, $j - $i - 1);
			$annotation_class = self::getAnnotationClassName($reflection_object, $annotation_name);
			$multiple = is_a($annotation_class, Multiple_Annotation::class, true);
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
	 * @param $reflection_object Has_Doc_Comment
	 * @param $annotation_name string
	 * @return string
	 */
	private static function getAnnotationClassName(
		Has_Doc_Comment $reflection_object, $annotation_name
	) {
		$reflection_class = get_class($reflection_object);
		$pos = strrpos($reflection_class, '_');
		$reflection_class = substr($reflection_class, $pos + 1);
		if ($reflection_class == 'Class') {
			$reflection_class .= '_';
		}
		return __NAMESPACE__
			. BS . $reflection_class
			. BS . Names::propertyToClass($annotation_name) . '_Annotation';
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
			case SP: case TAB:
				$i ++;
				$j = strpos($doc_comment, LF, $i);
				$value = trim(substr($doc_comment, $i, $j - $i));
				break;
			case CR: case LF:
				$value = true;
				break;
			default:
				$value = null;
		}
		return isset($value) ? new $annotation_class($value, $reflection_object) : null;
	}

}
