<?php
namespace ITRocks\Framework\Examples\Component\Static_Table;

use ITRocks\Framework\Component\Static_Table;
use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * Class Example_Controller
 * /ITRocks/Framework/Examples/Component/Static_Table/example
 */
class Example_Controller extends Default_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name, $feature_name)
	{
		$static_table = new Static_Table(
			['Nom 1', 'Nom-Prenom 1', 'Nom 2', 'Nom-Prenom 2'],
			[
				['Yves Delsarte', 'Jean-Charles Hauet', 'Christopher Jaubert', 'Tobie Alard'],
				['Joël Raoult', 'Jean-Charles Hauet', 'Natanaël De Villepin', 'Loup Baudelaire'],
				['Ernest Vaugeois', 'Néo Beaugendre', 'Hugo De la Croix', 'Mathéo Bachelot'],
				['Tobie Clérisseau', 'Edouard Aliker', 'Jacob Pernet', 'Hervé Cormier'],
			]
		);
		return View::run(
			[Static_Table::COMPONENT_NAME => $static_table], [], [], $class_name, $feature_name
		);
	}

}
