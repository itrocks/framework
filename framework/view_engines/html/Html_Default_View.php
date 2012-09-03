<?php
namespace Framework;

class Html_Default_View implements IView
{

	//------------------------------------------------------------------------------------------- run
	public function run($parameters, $form, $files, $class_name, $feature_name)
	{
		$templates_files = Html_View_Engine::getPossibleTemplates($class_name, $feature_name);
		foreach ($templates_files as $template_file) {
			$template_file = stream_resolve_include_path($template_file);
			if ($template_file) {
				if (!$parameters) {
					foreach (Application::getNamespaces() as $namespace) {
						$class = $namespace . "\\" . $class_name;
						if (@class_exists($class)) {
							$parameters = array($class_name => new $class());
							break;
						}
					}
				}
				$template = new Html_Template(reset($parameters), $template_file, $feature_name);
				echo $template->parse();
				break;
			}
		}
	}

}
