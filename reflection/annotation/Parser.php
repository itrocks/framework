<?php
namespace ITRocks\Framework\Reflection\Annotation;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\PHP;
use ITRocks\Framework\Property\Reflection_Property;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Annotation_In;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class_Component;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;

/**
 * The annotation parser process calculates the annotation value
 */
class Parser
{

	//-------------------------------------------------------------------------------- DOC_COMMENT_IN
	const DOC_COMMENT_IN = "\t *IN ";

	//---------------------------------------------------------------- annotations contexts constants
	const T_CLASS    = 'Class_';
	const T_METHOD   = 'Method';
	const T_PROPERTY = 'Property';

	//-------------------------------------------------------------------------- $default_annotations
	/**
	 * @var string[]
	 */
	public static $default_annotations;

	//-------------------------------------------------------------------------------- allAnnotations
	/**
	 * Parses all annotations of a reflection object
	 * Warning : only returns annotation in doc comments (not default annotations)
	 *
	 * @param $reflection_object Has_Doc_Comment|Annoted
	 * @return Annotation[]|array
	 */
	public static function allAnnotations(Has_Doc_Comment $reflection_object)
	{
		$doc_comment = $reflection_object->getDocComment([T_EXTENDS, T_IMPLEMENTS, T_USE]);
		$annotations = [];
		$i           = 0;
		while (($i = strpos($doc_comment, '* @', $i)) !== false) {
			$i += 2;
			$j  = strlen($doc_comment);
			if (($k = strpos($doc_comment, LF, $i)) < $j) $j = $k;
			if (($k = strpos($doc_comment, SP, $i)) < $j) $j = $k;
			$annotation_name  = substr($doc_comment, $i + 1, $j - $i - 1);
			$annotation_class = static::getAnnotationClassName(
				get_class($reflection_object), $annotation_name
			);
			$multiple = is_a($annotation_class, Multiple_Annotation::class, true);
			if ($multiple || !isset($annotations[$annotation_name])) {
				if ($reflection_object->isAnnotationCached($annotation_name, $multiple)) {
					$annotations[$annotation_name] = $multiple
						? $reflection_object->getAnnotations($annotation_name)
						: $reflection_object->getAnnotation($annotation_name);
				}
				else {
					$annotation = self::parseAnnotationValue(
						$doc_comment, $annotation_name, $i, $annotation_class, $reflection_object
					);
				}
				if (isset($annotation)) {
					if ($multiple) {
						$annotations[$annotation_name][] = $annotation;
					}
					else {
						$annotations[$annotation_name] = $annotation;
					}
				}
			}
		}
		foreach ($reflection_object->getCachedAnnotations() as $annotation_name => $cached_annotation) {
			$annotation = $cached_annotation[0];
			if (isset($annotations[$annotation_name])) {
				$annotations[$annotation_name] = $annotation;
			}
		}
		return $annotations;
	}

	//---------------------------------------------------------------------------------------- byName
	/**
	 * Parse a given annotation from a reflection class / method / property / etc. doc comment
	 *
	 * @param $reflection_object Has_Doc_Comment
	 * @param $annotation_name   string
	 * @param $multiple          boolean if null, multiple automatically set if annotation class is a
	 *                           Multiple_Annotation
	 * @param $local             boolean if true, only local doc-comments is read
	 * @return Annotation|Annotation[]
	 */
	public static function byName(
		Has_Doc_Comment $reflection_object, $annotation_name, $multiple = null, $local = false
	) {
		$annotation_class = static::getAnnotationClassName(
			get_class($reflection_object), $annotation_name
		);
		if (!isset($multiple)) {
			$multiple = is_a($annotation_class, Multiple_Annotation::class, true);
		}
		$doc_comment = $local
			? (
				($reflection_object instanceof Reflection_Property)
					? $reflection_object->getDocComment([], false)
					: $reflection_object->getDocComment([])
				)
			: $reflection_object->getDocComment([T_EXTENDS, T_IMPLEMENTS, T_USE]);
		$annotations = [];
		$annotation  = null;
		$i           = 0;
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
		$annotation = $multiple ? self::multipleRemove($annotations) : (
			$annotation ? $annotation : new $annotation_class(null, $reflection_object, $annotation_name)
		);
		return $annotation;
	}

	//------------------------------------------------------------------------ getAnnotationClassName
	/**
	 * Gets annotation class name (including namespace) for a given annotation name
	 *
	 * @param $class_name      string eg Reflection_Class, Reflection_Method, Reflection_Property
	 * @param $annotation_name string
	 * @return string
	 */
	public static function getAnnotationClassName($class_name, $annotation_name)
	{
		static $cache = [];
		while (!in_array(
			rLastParse($class_name, BS),
			['Reflection_Class', 'Reflection_Method', 'Reflection_Property']
		)) {
			$class_name = get_parent_class($class_name);
		}
		if (!isset($cache[$class_name][$annotation_name])) {
			$reflection_class = $class_name;
			$pos              = strrpos($reflection_class, '_');
			$reflection_class = substr($reflection_class, $pos + 1);
			if ($reflection_class == 'Class') {
				$reflection_class .= '_';
			}
			elseif ($reflection_class == 'Value') {
				$reflection_class = 'Property';
			}
			if (!isset(self::$default_annotations)) {
				self::initDefaultAnnotations();
			}
			if (isset(self::$default_annotations[$reflection_class . '@' . $annotation_name])) {
				$annotation_class = self::$default_annotations[$reflection_class . '@' . $annotation_name];
			}
			else {
				$annotation_class = __NAMESPACE__
					. BS . $reflection_class
					. BS . Names::propertyToClass($annotation_name) . '_Annotation';
				if (!class_exists($annotation_class)) {
					$annotation_class = Annotation::class;
				}
			}
			$cache[$class_name][$annotation_name] = $annotation_class;
		}
		return $cache[$class_name][$annotation_name];
	}

	//------------------------------------------------------------------------ initDefaultAnnotations
	/**
	 * Init self::$default_annotations with cached file content
	 */
	private static function initDefaultAnnotations()
	{
		if (!self::$default_annotations) {
			if (is_dir(Application::getCacheDir())) {
				$default_annotations_file = Application::getCacheDir() . SL . 'default_annotations.php';
				clearstatcache(true, $default_annotations_file);
				$file_time = file_exists($default_annotations_file)
					? filemtime($default_annotations_file)
					: 0;
				if (!$file_time || ($file_time < filemtime(__DIR__ . SL . 'default_annotations.php'))) {
					copy(__DIR__ . SL . 'default_annotations.php', $default_annotations_file);
				}
			}
			else {
				$default_annotations_file = __DIR__ . SL . 'default_annotations.php';
			}
			/** @noinspection PhpIncludeInspection dynamic */
			include_once $default_annotations_file;
		}
	}

	//-------------------------------------------------------------------------------- multipleRemove
	/**
	 * Remove annotations from the collection having value if followed by annotations having !value
	 *
	 * @param $annotations Annotation[]
	 * @return Annotation[]
	 */
	private static function multipleRemove(array $annotations)
	{
		$remove = [];
		foreach ($annotations as $key => $annotation) {
			if (is_string($annotation->value)) {
				if (substr($annotation->value, 0, 1) === '!') {
					$remove[$annotation->value] = true;
					unset($annotations[$key]);
				}
				elseif (isset($remove[$annotation->value])) {
					unset($annotations[$key]);
				}
			}
		}
		return $annotations;
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
			case SP:
			case TAB:
				$i ++;
				$j = strlen($doc_comment);
				$next_annotation = strpos($doc_comment, SP . '* @', $i);
				$end_doc_comment = strpos($doc_comment, SP . '*/', $i);
				$next_in         = strpos($doc_comment, LF . self::DOC_COMMENT_IN, $i);
				if (($next_annotation !== false) && ($next_annotation < $j)) $j = $next_annotation;
				if (($end_doc_comment !== false) && ($end_doc_comment < $j)) $j = $end_doc_comment;
				if (($next_in         !== false) && ($next_in         < $j)) $j = $next_in;
				if ($j === false) {
					trigger_error('Missing doc_comment end', E_USER_ERROR);
				}
				$value = trim(preg_replace('%\s*\n\s+\*\s*%', SP, substr($doc_comment, $i, $j - $i)));
				break;
			case CR:
			case LF:
				$value = true;
				break;
			default:
				$value = null;
		}
		/** @var $annotation Annotation */
		$annotation = isset($value)
			? new $annotation_class($value, $reflection_object, $annotation_name)
			: null;

		if (isset($annotation) && isA($annotation, Annotation_In::class)) {
			/** @var $annotation Annotation_In */
			$j = strrpos(substr($doc_comment, 0, $i), LF . self::DOC_COMMENT_IN);
			if ($j === false) {
				$annotation->class_name = ($reflection_object instanceof Reflection_Class_Component)
					? $reflection_object->getDeclaringClassName()
					: $reflection_object->getName();
			}
			else {
				$j                     += strlen(self::DOC_COMMENT_IN) + 1;
				$k                      = strpos($doc_comment, LF, $j);
				$annotation->class_name = substr($doc_comment, $j, $k - $j);
			}
		}

		if (isset($annotation) && isA($annotation, Types_Annotation::class)) {
			/** @var $annotation Types_Annotation */
			$do = false;
			if (is_array($annotation->value)) {
				foreach ($annotation->value as $value) {
					if ($value && (ctype_upper($value[0]) || ($value[0] == BS))) {
						$do = true;
						break;
					}
				}
			}
			else {
				$do = $annotation->value && ctype_upper($annotation->value[0]);
			}
			if ($do) {
				$j = strrpos(substr($doc_comment, 0, $i), LF . self::DOC_COMMENT_IN);
				if ($j === false) {
					$class_name = ($reflection_object instanceof Reflection_Class_Component)
						? $reflection_object->getDeclaringClassName()
						: $reflection_object->getName();
					$namespace = Namespaces::of($class_name);
					$use       = PHP\Reflection_Class::of($class_name)->getNamespaceUse();
				}
				else {
					$j        += strlen(self::DOC_COMMENT_IN) + 1;
					$k         = strpos($doc_comment, LF, $j);
					$in_class  = substr($doc_comment, $j, $k - $j);
					$namespace = Namespaces::of($in_class);
					$use       = PHP\Reflection_Class::of($in_class)->getNamespaceUse();
				}
				$annotation->applyNamespace($namespace, $use);
			}
			elseif (is_array($annotation->value)) {
				foreach ($annotation->value as $key => $value) {
					$annotation->value[$key] = Builder::className($value);
				}
			}
			else {
				if ($annotation->value && ($annotation->value[0] === BS)) {
					$annotation->value = substr($annotation->value, 1);
				}
				$annotation->value = Builder::className($annotation->value);
			}
		}

		if (
			is_a($annotation_class, Do_Not_Inherit::class, true)
			&& (strpos($doc_comment, self::DOC_COMMENT_IN) !== false)
			&& ($i > strpos($doc_comment, self::DOC_COMMENT_IN))
		) {
			return null;
		}

		return $annotation;
	}

}
