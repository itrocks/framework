<?php
namespace ITRocks\Framework\Widget\Output;

use ITRocks\Framework\Tools\Json;
use ITRocks\Framework\View\Json\Default_View;

/**
 * @var $class_name      string
 * @var $feature_name    string
 * @var $files           array[]
 * @var $form            array
 * @var $parameters      array
 * @var $this            Default_View
 */

$result = Json::toJson(Json::toStdObject($parameters[$class_name]));

$this->json = $result;
