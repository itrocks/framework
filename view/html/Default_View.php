<?php
namespace ITRocks\Framework\View\Html;

use Exception;
use ITRocks\Framework;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\View\IView;
use ReflectionException;

/**
 * The Html default view selects the template associated to wished class and feature names
 */
class Default_View implements IView
{

	//------------------------------------------------------------------------------- executeTemplate
	/**
	 * @param $template_file string
	 * @param $parameters    array
	 * @param $feature_name  string
	 * @return string
	 * @throws Exception
	 * @throws ReflectionException
	 */
	protected function executeTemplate($template_file, array $parameters, $feature_name)
	{
		if (isset($parameters[Template::TEMPLATE])) {
			unset($parameters[Template::TEMPLATE]);
		}
		if (isset($parameters[Template::TEMPLATE_CLASS])) {
			$template_class = $parameters[Template::TEMPLATE_CLASS];
		}
		elseif (isset($parameters[Template::TEMPLATE_NAMESPACE])) {
			$template_class = $parameters[Template::TEMPLATE_NAMESPACE] . BS . 'Html_Template';
			unset($parameters[Template::TEMPLATE_NAMESPACE]);
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
	 * @param $files        array[]
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return string
	 * @throws ReflectionException
	 */
	public function run(array $parameters, array $form, array $files, $class_name, $feature_name)
	{
		$feature_names
			= (isset($parameters[Feature::FEATURE]) && ($parameters[Feature::FEATURE] !== $feature_name))
			? [$parameters[Feature::FEATURE], $feature_name]
			: [$feature_name];
		$template_file = Engine::getTemplateFile(
			$class_name,
			$feature_names,
			isset($parameters[Template::TEMPLATE]) ? $parameters[Template::TEMPLATE] : null
		);
		return self::executeTemplate($template_file, $parameters, $feature_name);
	}

}
