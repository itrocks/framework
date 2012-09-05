<?php
namespace SAF\Framework;

interface IView
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param multitype:object $parameters
	 * @param array $form
	 * @param array $files
	 * @param string $class_name
	 * @param string $feature_name
	 */
	public function run($parameters, $form, $files, $class_name, $feature_name);

}
