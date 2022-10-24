<?php
namespace ITRocks\Framework\View\Html;

use ITRocks\Framework;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\View\IView;

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

		// TODO LOW better call an 'error 500' page
		$set = reset($parameters);
		if (($set instanceof Set) && !class_exists($set->element_class_name)) {
			$parameters[Template::HIDE_PAGE_FRAME] = true;
			$template_file = __DIR__ . '/../../feature/blank/blank.html';
		}

		$template = $this->newTemplate($template_class, $parameters, $template_file, $feature_name);
		return $template->parse();
	}

	//----------------------------------------------------------------------------------- newTemplate
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $template_class string
	 * @param $parameters     array
	 * @param $template_file  string
	 * @param $feature_name   string
	 * @return Template
	 */
	protected function newTemplate($template_class, array $parameters, $template_file, $feature_name)
	{
		/** @var $template Template */
		/** @noinspection PhpUnhandledExceptionInspection $template_class must be valid */
		$template = Builder::create(
			$template_class, [reset($parameters), $template_file, $feature_name]
		);

		$template->setParameters($parameters);
		$current = Framework\View::current();
		if (($current instanceof Engine) && ($css = $current->getCss())) {
			$template->setCss($css);
		}
		return $template;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   array
	 * @param $form         array
	 * @param $files        array[]
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return ?string
	 */
	public function run(
		array $parameters, array $form, array $files, string $class_name, string $feature_name
	) : ?string
	{
		$feature_names
			= (isset($parameters[Feature::FEATURE]) && ($parameters[Feature::FEATURE] !== $feature_name))
			? [$parameters[Feature::FEATURE], $feature_name]
			: [$feature_name];
		$template_file = Engine::getTemplateFile(
			$class_name ?: Engine::class,
			$feature_names,
			$parameters[Template::TEMPLATE] ?? null
		);
		return $this->executeTemplate($template_file, $parameters, $feature_name);
	}

}
