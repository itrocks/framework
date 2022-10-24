<?php
namespace ITRocks\Framework\View;

/**
 * The common interface for all view classes
 */
interface IView
{

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
	) : ?string;

}
