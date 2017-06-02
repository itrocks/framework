<?php
namespace ITRocks\Framework\Examples\Car;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Widget\Add;

/**
 * Car add controller
 */
class Add_Controller extends Add\Add_Controller
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
		$image = new Reflection_Property($class_name, 'image');
		$image->setAnnotation('alias', new Alias_Annotation('web image', $image));

		return parent::getViewParameters($parameters, $form, $class_name);
	}

}
