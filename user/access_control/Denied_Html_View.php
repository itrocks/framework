<?php
namespace ITRocks\Framework\User\Access_Control;

use ITRocks\Framework\View\Html\Default_View;

/**
 * Blank HTML view :
 * Not totally blank : show remote IP (caller) and URI (called)
 */
class Denied_Html_View extends Default_View
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   array
	 * @param $form         array
	 * @param $files        array[]
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(array $parameters, array $form, array $files, $class_name, $feature_name)
	{
		$parameters['host']   = $_SERVER['HTTP_HOST'];
		$parameters['remote'] = $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'];
		$parameters['uri']    = $_SERVER['REQUEST_URI'];
		return parent::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
