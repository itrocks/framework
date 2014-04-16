<?php
namespace SAF\Framework\View\Html;

use SAF\Framework\Builder;
use SAF\Framework;
use SAF\Framework\View\IView;

/**
 * The Html default view selects the template associated to wished class and feature names
 */
class Default_View implements IView
{

	//------------------------------------------------------------------------------- executeTemplate
	/**
	 * @param $template_file string
	 * @param $parameters    array
	 * @param $feature_name string
	 * @return string
	 */
	private function executeTemplate($template_file, $parameters, $feature_name)
	{
		if (isset($parameters['template'])) {
			unset($parameters['template']);
		}
		if (isset($parameters['template_namespace'])) {
			$template_class = $parameters['template_namespace'] . BS . 'Html_Template';
			unset($parameters['template_namespace']);
		}
		else {
			$template_class = Template::class;
		}
		/** @var $template Template */
		$template = Builder::create(
			$template_class, [reset($parameters), $template_file, $feature_name]
		);
		$template->setParameters($parameters);
		$current = Framework\View::current();
		if (($current instanceof Engine) && ($css = $current->getCss())) {
			$template->setCss($css);
		}
		return $template->parse();
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   array
	 * @param $form         array
	 * @param $files        array
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return string
	 */
	public function run($parameters, $form, $files, $class_name, $feature_name)
	{
		$feature_names = (isset($parameters['feature']) && ($parameters['feature'] !== $feature_name))
			? [$parameters['feature'], $feature_name]
			: [$feature_name];
		$template_file = Engine::getTemplateFile(
			$class_name, $feature_names, isset($parameters['template']) ? $parameters['template'] : null
		);
		return self::executeTemplate($template_file, $parameters, $feature_name);
	}

}
