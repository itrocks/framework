<?php
namespace SAF\Framework\Builder;

use SAF\Framework\Application;
use SAF\Framework\Builder;
use SAF\Framework\Class_Builder;
use SAF\Framework\Files;

/**
 * Built classes compiler
 */
class Compiler
{

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $replacements string[]
	 * @return string[]
	 */
	public function compile($replacements)
	{
		$cache_dir = Application::current()->getCacheDir();
		foreach ($replacements as $class_name => $replacement) {
			if (is_array($replacement)) {
				$built_name = null;
				foreach (Class_Builder::build($class_name, $replacement, true) as $built_name => $source) {
					$source = '<?php' . "\n" . $source;

					$path = array_slice(explode('\\', $built_name), 2);
					$file_name = array_pop($path) . '.php';
					$path = $cache_dir . '/' . strtolower(join('/', $path));
					Files::mkdir($path);

					script_put_contents($path . '/' . $file_name, $source);
				}
				$replacements[$class_name] = $built_name;
			}
		}
		return $replacements;
	}

}
