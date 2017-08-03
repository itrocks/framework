<?php
namespace ITRocks\Framework\Export;

use ITRocks\Framework\Application;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Widget\Data_List\Selection;

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

		$selection  = new Selection($parameters, $form);
		$data       = $selection->readDataSelect();
		$properties = $data->getProperties();

		// create temporary file
		/** @var $application Application */
		$application   = Session::current()->get(Application::class);
		$tmp           = $application->getTemporaryFilesPath();
		$short_class   = Names::classToProperty($class_name);
		$csv_file_name = tempnam($tmp, $short_class . '_') . '.csv';
		$file          = fopen($csv_file_name, 'w');

		// write first line (properties path)
		$row = [];
		foreach ($selection->getDataListSettings()->properties as $property) {
			if (isset($properties[$property->path])) {
				$row[] = $property->shortTitle();
			}
		}
		fputcsv($file, $row);

		// format dates
		foreach ($properties as $property) {
			if ($property instanceof Reflection_Property) {
				if ($property->getType()->isDateTime()) {
					$date_times[$property->path] = true;
				}
				if ($property->getListAnnotation('values')->values()) {
					$translate[$property->path] = true;
				}
			}
		}

		// write data
		foreach ($data->getRows() as $row) {
			$write = [];
			foreach ($row->getValues() as $property_path => $value) {
				if (isset($date_times[$property_path])) {
					if ($value === '0000-00-00 00:00:00') {
						$value = null;
					}
					elseif (substr($value, -8) === '00:00:00') {
						$value = lParse($value, SP);
					}
				}
				elseif (isset($translate[$property_path])) {
					$value = Loc::tr($value);
				}
				$write[] = $value;
			}
			fputcsv($file, $write);
		}

		// done
		fclose($file);

		// simple convert to XLSX using gnumeric
		$xlsx_file_name = str_replace('.csv', '.xlsx', $csv_file_name);
		exec(
			'ssconvert --import-encoding=UTF8'
			. SP . DQ . $csv_file_name . DQ
			. SP . DQ . $xlsx_file_name . DQ
			. SP . '2>&1 &'
		);

		// download
		$output = file_get_contents($xlsx_file_name);
		unlink($xlsx_file_name);
		unlink($csv_file_name);
		Files::downloadOutput(($short_class . '.xlsx'), 'xlsx', strlen($output));

		return $output;
	}

}
