<?php
namespace SAF\Framework\Export;

use SAF\Framework\Application;
use SAF\Framework\Builder;
use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Session;
use SAF\Framework\Tools\Files;
use SAF\Framework\Tools\Names;
use SAF\Framework\View;
use SAF\Framework\Widget\Data_List\Data_List_Controller;
use SAF\Framework\Widget\Data_List_Setting\Data_List_Settings;

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
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
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
		$data = $data_list_controller->readData($class_name, $list_settings);
		// create temporary file
		/** @var $application Application */
		$application = Session::current()->get(Application::class);
		$tmp = $application->getTemporaryFilesPath();
		$csv_file_name = $tmp . SL . Names::classToProperty($class_names) . '.csv';
		$f = fopen($csv_file_name, 'w');
		// write first line (properties path)
		$row = [];
		foreach ($list_settings->properties as $property) {
			$row[] = $property->shortTitle();
		}
		fputcsv($f, $row);
		// write data
		foreach ($data->getRows() as $row) {
			fputcsv($f, $row->getValues());
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
		Files::downloadOutput(rLastParse($xlsx_file_name, SL), 'xlsx', strlen($output));
		return $output;
	}

}
