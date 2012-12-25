<?php
namespace SAF\Framework;

class Html_Default_View implements IView
{

	//------------------------------------------------------------------------------------------- run
	public function run($parameters, $form, $files, $class_name, $feature_name)
	{
		$templates_files = Html_View_Engine::getPossibleTemplates(
			Namespaces::shortClassName($class_name),
			$feature_name
		);
		foreach ($templates_files as $template_file) {
			$template_file = stream_resolve_include_path($template_file);
			if ($template_file) {
				if (isset($parameters["template_mode"])) {
					$template_class = Namespaces::fullClassName(
						"Html_" . Names::propertyToClass($parameters["template_mode"]) . "_Template"
					);
					unset($parameters["template_mode"]);
				}
				else {
					$template_class = __NAMESPACE__ . "\\Html_Template";
				}
				$template = new $template_class(reset($parameters), $template_file, $feature_name);
				$template->setParameters($parameters);
				if ($css = View::current()->getCss()) {
					$template->setCss($css);
				}
				echo $template->parse();
				break;
			}
		}
	}

}
