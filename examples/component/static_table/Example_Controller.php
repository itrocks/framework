<?php
namespace ITRocks\Framework\Examples\Component\Static_Table;

use ITRocks\Framework\Component\Static_Table;
use ITRocks\Framework\Controller\Default_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Dom\Table;

/**
 * Class Example_Controller
 * /ITRocks/Framework/Examples/Component/Static_Table/example
 */
class Example_Controller extends Default_Controller
{

	//--------------------------------------------------------------------------------------- getBody
	/**
	 * @return Table\Standard_Cell[][]
	 */
	protected function getBody() : array
	{
		return [
			[
				new Table\Standard_Cell('Yves Delsarte'), new Table\Standard_Cell('Jean-Charles Hauet'),
				new Table\Standard_Cell('Christopher Jaubert'), new Table\Standard_Cell('Tobie Alard'),
			],
			[
				new Table\Standard_Cell('Joël Raoult'), new Table\Standard_Cell('Natanaël De Villepin'),
				new Table\Standard_Cell('Jean-Charles Hauet'), new Table\Standard_Cell('Loup Baudelaire'),
			],
			[
				new Table\Standard_Cell('Ernest Vaugeois'), new Table\Standard_Cell('Néo Beaugendre'),
				new Table\Standard_Cell('Hugo De la Croix'), new Table\Standard_Cell('Mathéo Bachelot'),
			],
			[
				new Table\Standard_Cell('Tobie Clérisseau'), new Table\Standard_Cell('Edouard Aliker'),
				new Table\Standard_Cell('Jacob Pernet'), new Table\Standard_Cell('Hervé Cormier'),
			],
		];
	}

	//------------------------------------------------------------------------------------- getFooter
	/**
	 * @return Table\Standard_Cell[][]
	 */
	protected function getFooter() : array
	{
		$first_cell = new Table\Standard_Cell('Lorem ipsum dolor sit amet');
		$first_cell->setAttribute('colspan', 3);
		$last_cell = new Table\Standard_Cell('itrocks.org');
		$last_cell->setAttribute('colspan', 4);
		$last_cell->addClass('bold-cell');
		return [
			[$first_cell, new Table\Standard_Cell('Excepteur sint occaecat cupidatat non')],
			[$last_cell]
		];
	}

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
			[
				new Table\Header_Cell('Nom 1'), new Table\Header_Cell('Nom-Prenom 1'),
				new Table\Header_Cell('Nom 2'), new Table\Header_Cell('Nom-Prenom 2')
			],
			$this->getBody(),
			$this->getFooter()
		);
		return View::run(
			[Static_Table::COMPONENT_NAME => $static_table], [], [], $class_name, $feature_name
		);
	}

}
