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
class Controller implements Default_Feature_Controller
{

	//-------------------------------------------------------------------------------- ALL_PROPERTIES
	const ALL_PROPERTIES = 'all_properties';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		// TODO #81421 (SM)
		upgradeMemoryLimit('6G');
		upgradeTimeLimit(3600);

		$export = new Export($class_name, $parameters, $form);
		$export->all_properties = $parameters->isTrue(static::ALL_PROPERTIES, true);
		return $export->export();
	}

}
