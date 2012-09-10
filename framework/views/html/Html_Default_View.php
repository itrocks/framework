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
				if (!$parameters) {
					$parameters = array($class_name => new $class_name());
				}
				$template = new Html_Template(reset($parameters), $template_file, $feature_name);
				echo $template->parse();
				break;
			}
		}
	}

}
