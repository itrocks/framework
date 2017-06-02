<?php
namespace ITRocks\Framework\Examples\Car;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Widget\Add;

/**
 * Car element edit controller
 */
class Element_Add_Controller extends Add\Add_Controller
{

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Parameters $parameters, array $form, $class_name)
	{
		$name        = new Reflection_Property($class_name, 'name');
		$name_values = Values_Annotation::of($name);
		$name_values->add('door');
		$name_values->add('motor');
		$name_values->add('wheel');
		$name_values->add('window');
		$name->setAnnotation($name_values);

		return parent::getViewParameters($parameters, $form, $class_name);
	}

}
