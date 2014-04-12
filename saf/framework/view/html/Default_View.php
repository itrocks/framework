<?php
namespace SAF\Framework\View\Html;

use SAF\Framework\Builder;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\View\IView;
use SAF\Framework\View;

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
		if (isset($parameters['template_mode'])) {
			$template_class = Namespaces::fullClassName(
				'Html_' . Names::propertyToClass($parameters['template_mode']) . '_Template'
			);
			unset($parameters['template_mode']);
		}
		else {
			$template_class = Template::class;
		}
		/** @var $template Template */
		$template = Builder::create(
			$template_class, [reset($parameters), $template_file, $feature_name]
		);
		$template->setParameters($parameters);
		$current = View::current();
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
		$feature_names = isset($parameters['feature'])
			? [$parameters['feature'], $feature_name]
			: [$feature_name];
		$template_file = Engine::getTemplateFile($class_name, $feature_names);
		return self::executeTemplate($template_file, $parameters, $feature_name);
	}

}
