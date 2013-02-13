<?php
namespace SAF\Framework;

interface IView
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters array
	 * @param $form array
	 * @param $files array
	 * @param $class_name string
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run($parameters, $form, $files, $class_name, $feature_name);

}
