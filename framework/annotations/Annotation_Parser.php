<?php

class Annotation_Parser
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
		$doc_comment = str_replace("\r", "", $doc_comment);
		if (strpos($doc_comment, "@$annotation_name ")) {
			$found = true;
			$value = mParse($doc_comment, "@$annotation_name ", "\n");
		} elseif (strpos($doc_comment, "@$annotation_name\n")) {
			$found = true;
			$value = true;
		} else {
			$found = false;
		}
		if ($found) {
			$annotation_class = ucfirst($annotation_name) . "_Annotation";
			if (class_exists($annotation_class)) {
				return new $annotation_class($value);
			} else {
				return new Annotation($value);
			}
		} else {
			return null;
		}
	}

}
