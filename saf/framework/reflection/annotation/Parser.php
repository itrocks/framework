<?php
namespace SAF\Framework\Reflection\Annotation;

use SAF\Framework\Builder;
use SAF\Framework\PHP;
use SAF\Framework\Reflection\Annotation;
use SAF\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Types_Annotation;
use SAF\Framework\Reflection\Interfaces\Has_Doc_Comment;
use SAF\Framework\Reflection\Interfaces\Reflection;
use SAF\Framework\Reflection\Interfaces\Reflection_Class_Component;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;

/**
 * The annotation parser process calculates the annotation value
 */
abstract class Parser
{

	//---------------------------------------------------------------------------------- $annotations
	const DOC_COMMENT_IN = '***IN ';

	//---------------------------------------------------------------------------------- $annotations
	/**
	 * @var string[]
	 */
	public static $default_annotations;

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
		include_once __DIR__ . '/default_annotations.php';
		$annotation_class = static::getAnnotationClassName($reflection_object, $annotation_name);
		if (
			!@class_exists($annotation_class)
			&& isset(self::$default_annotations[$annotation_class])
		) {
			$annotation_class = self::$default_annotations[$annotation_class];
		}
		if (!isset($multiple)) {
			$multiple = is_a($annotation_class, Multiple_Annotation::class, true);
		}
		$doc_comment = $reflection_object->getDocComment([T_EXTENDS, T_IMPLEMENTS, T_USE]);
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
		$doc_comment = $reflection_object->getDocComment([T_EXTENDS, T_IMPLEMENTS, T_USE]);
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
		elseif ($reflection_class == 'Value') {
			$reflection_class = 'Property';
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
	 * @param $reflection_object Has_Doc_Comment|Reflection
	 * @return Annotation
	 */
	private static function parseAnnotationValue(
		$doc_comment, $annotation_name, &$i, $annotation_class, Reflection $reflection_object
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
		/** @var $annotation Annotation */
		$annotation = isset($value) ? new $annotation_class($value, $reflection_object) : null;

		if (isset($annotation) && isA($annotation, Types_Annotation::class)) {
			$do = false;
			if (is_array($annotation->value)) {
				foreach ($annotation->value as $value) {
					if ($value && ctype_upper($value[0])) {
						$do = true;
						break;
					}
				}
			}
			else {
				$do = $annotation->value && ctype_upper($annotation->value[0]);
			}
			if ($do) {
				/** @var $annotation Types_Annotation */
				$j = strrpos(substr($doc_comment, 0, $i), LF . self::DOC_COMMENT_IN);
				if ($j === false) {
					$class_name = ($reflection_object instanceof Reflection_Class_Component)
						? $reflection_object->getDeclaringClassName()
						: $reflection_object->getName();
					$namespace = Namespaces::of($class_name);
					$use = PHP\Reflection_Class::of($class_name)->getNamespaceUse();
				}
				else {
					$j += strlen(self::DOC_COMMENT_IN) + 1;
					$k = strpos($doc_comment, LF, $j);
					$in_class = substr($doc_comment, $j, $k - $j);
					$namespace = Namespaces::of($in_class);
					$use = PHP\Reflection_Class::of($in_class)->getNamespaceUse();
				}
				$annotation->applyNamespace($namespace, $use);
			}
			elseif (is_array($annotation->value)) {
				foreach ($annotation->value as $key => $value) {
					$annotation->value[$key] = Builder::className($value);
				}
			}
			else {
				$annotation->value = Builder::className($annotation->value);
			}
		}

		return $annotation;
	}

}
