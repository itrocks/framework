<?php
namespace ITRocks\Framework\Export;

use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Widget\Data_List\Data_List_Controller;
use ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings;

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
		/** @todo cf task #81421  */
		upgradeMemoryLimit('6G');
		upgradeTimeLimit(3600);

		// get list settings
		$class_names = $class_name;
		$class_name = $parameters->getMainObject()->element_class_name;
		$list_settings = Data_List_Settings::current($class_name);
		$list_settings->cleanup();
		// read data
		/** @var $data_list_controller Data_List_Controller */
		$data_list_class_name = Main::$current->getController($class_name, 'dataList')[0];
		$data_list_controller = Builder::create($data_list_class_name);
		$list_settings->maximum_displayed_lines_count = null;
		// SM : Now called here instead of inside readData to use $search below
		$search = $data_list_controller->applySearchParameters($list_settings);

		if (isset($form['select_all']) && $form['select_all']) {
			if (isset($form['excluded_selection']) && $form['excluded_selection']) {
				$excluded       = explode(',', $form['excluded_selection']);
				$search[]['id'] = Func::notIn($excluded);
			}
		}
		else if (isset($form['selection']) && $form['selection']) {
			$selected       = explode(',', $form['selection']);
			$search[]['id'] = Func::in($selected);
		}
		else {
			$search = ['id' => -1];
		}

		$data = $data_list_controller->readData(
			$class_name, $list_settings, $search, [$list_settings->sort]
		);
		// create temporary file
		/** @var $application Application */
		$application = Session::current()->get(Application::class);
		$tmp = $application->getTemporaryFilesPath();
		$short_class = Names::classToProperty($class_names);
		$csv_file_name = tempnam($tmp, $short_class . '_') . '.csv';
		$f = fopen($csv_file_name, 'w');
		// write first line (properties path)
		$row = [];
		foreach ($list_settings->properties as $property) {
			$row[] = $property->shortTitle();
		}
		fputcsv($f, $row);
		// format dates
		foreach ($data->getProperties() as $property) {
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
			fputcsv($f, $write);
		}
		// done
		fclose($f);
		// simple convert to XLSX using gnumeric
		$xlsx_file_name = str_replace('.csv', '.xlsx', $csv_file_name);
		exec('ssconvert --import-encoding=UTF8 ' . DQ . $csv_file_name . DQ . SP . DQ . $xlsx_file_name . DQ . ' 2>&1 &');
		// download
		$output = file_get_contents($xlsx_file_name);
		unlink($xlsx_file_name);
		unlink($csv_file_name);
		Files::downloadOutput(($short_class . '.xlsx'), 'xlsx', strlen($output));
		return $output;
	}

}
