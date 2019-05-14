<?php
namespace ITRocks\Framework\Feature\Export;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 * Default export controller
 *
 * Exports objects using the currently selected data list settings of the user
 * The default format is raw XLSX using fast csv generation then use of gnumeric for raw conversion
 *
 * TODO more formats, as a popup of the "export" button and as first $parameter
 * TODO xlsx with best formatting (please forget ssconvert, which do not allow that)
 */
class Export_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		// TODO #81421 (SM)
		upgradeMemoryLimit('6G');
		upgradeTimeLimit(3600);

		$export = new Export($class_name, $parameters, $form);
		return $export->export();
	}

}
