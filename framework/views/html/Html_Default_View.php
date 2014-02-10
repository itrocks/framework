<?php
namespace SAF\Framework;

/**
 * The Html default view selects the template associated to wished class and feature names
 */
class Html_Default_View implements IView
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
			$template_class = Html_Template::class;
		}
		/** @var $template Html_Template */
		$template = Builder::create(
			$template_class, array(reset($parameters), $template_file, $feature_name)
		);
		$template->setParameters($parameters);
		$current = View::current();
		if (($current instanceof Html_View_Engine) && ($css = $current->getCss())) {
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
		$templates_files = Html_View_Engine::getPossibleTemplates(
			Namespaces::shortClassName($class_name),
			isset($parameters['feature']) ? array($parameters['feature'], $feature_name) : $feature_name
		);
		foreach ($templates_files as $template_file) {
			if (!strpos($template_file, '.')) {
				$template_file = stream_resolve_include_path($template_file . '.html')
					?: stream_resolve_include_path($template_file . '.php');
			}
			if ($template_file) {
				return $this->executeTemplate($template_file, $parameters, $feature_name);
			}
		}
		return null;
	}

}
