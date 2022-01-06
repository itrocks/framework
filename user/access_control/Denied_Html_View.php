<?php
namespace ITRocks\Framework\User\Access_Control;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\User\Access_Control;
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
		$parameters['host'] = $_SERVER['HTTP_HOST'] ?? 'console';

		$parameters['remote'] = isset($_SERVER['REMOTE_ADDR'])
			? ($_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'])
			: 'console';

		$parameters['uri'] = $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_NAME'];

		$uri = new Uri(lParse(Uri::current(), '?'));
		$parameters['refused_class_name'] = Builder::current()->sourceClassName(
			get_class($uri->parameters->getMainObject())
		);
		$parameters['refused_object']  = $uri->parameters->getMainObject();
		$parameters['refused_feature'] = $uri->feature_name;

		if (
			($parameters['refused_object'] instanceof Access_Control)
			&& ($uri->feature_name === Feature::F_DENIED)
		) {
			$parameters['refused_class_name'] = '';
			$parameters['refused_feature']    = '';
			$parameters['refused_object']     = '';
		}

		return parent::run($parameters, $form, $files, $class_name, $feature_name);
	}

}
