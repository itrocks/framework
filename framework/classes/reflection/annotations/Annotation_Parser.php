<?php
namespace SAF\Framework;

require_once "framework/classes/reflection/annotations/Annotation.php";
require_once "framework/classes/reflection/annotations/templates/Multiple_Annotation.php";

abstract class Annotation_Parser
{

	//---------------------------------------------------------------------------------------- byName
	/**
	 * Parse a given annotation from a reflection class / method / property / etc. doc comment
	 * 
	 * @param  string $doc_comment
	 * @param  string $annotation_name
	 * @return Annotation
	 */
	public static function byName($doc_comment, $annotation_name)
	{
		$annotations = array();
		$annotation = null;
		$multiple = false;
		$i = 0;
		while (($i = strpos($doc_comment, "@" . $annotation_name, $i)) !== false) {
			$i += strlen($annotation_name) + 1;
			if (($doc_comment[$i] == " ") || ($doc_comment[$i] == "\t")) {
				$i ++;
				$j = strpos($doc_comment, "\n", $i);
				$value = trim(substr($doc_comment, $i, $j - $i));
			}
			else {
				$value = true;
			}
			if (!isset($annotation_class)) {
				$annotation_class = Namespaces::fullClassName(
					Names::propertyToClass($annotation_name) . "_Annotation"
				);
				if (!class_exists($annotation_class)) {
					$annotation_class = __NAMESPACE__ . "\\Annotation";
				}
				$multiple = is_subclass_of($annotation_class, __NAMESPACE__ . "\\Multiple_Annotation");
			}
			$annotation = new $annotation_class($value);
			$annotations[] = $annotation;
			if (!$multiple) {
				break;
			}
		}
		return $multiple ? $annotations : $annotation;
	}

}
