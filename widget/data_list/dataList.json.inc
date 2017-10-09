<?php
namespace ITRocks\Framework\Widget\Data_List;

use ITRocks\Framework\Tools\Default_List_Row;
use ITRocks\Framework\Tools\Json;
use ITRocks\Framework\View\Json\Default_View;
use stdClass;

/**
 * @var $class_name      string
 * @var $feature_name    string
 * @var $files           array[]
 * @var $form            array
 * @var $parameters      array
 * @var $this            Default_View
 */


$json              = new Json();
$json_array        = [];
$want_full_objects = isset($parameters['full']);

/** @var $element Default_List_Row */
foreach ($parameters[$class_name]->elements as $element) {
	// case want full objects
	if ($want_full_objects) {
		$std_object = $json->toStdObject($element->getObject());
	}
	// case want only columns of list settings (default)
	else {
		$std_object     = new stdClass();
		$std_object->id = $element->id();
		foreach($element->getValues() as $path => $value) {
			$std_object->{$path} = is_object($value) ? (string)$value : $value;
		}
	}
	$json_array[] = $std_object;
}

$result = $json->toJson($json_array);

$this->json = $result;
