<?php
namespace ITRocks\Framework\Feature\Export;

use ITRocks\Framework\Application;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Option\Translate;
use ITRocks\Framework\Feature\List_\Selection;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Files;
use ITRocks\Framework\Tools\Names;

/**
 * Export feature
 */
class Export
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//------------------------------------------------------------------------------------ $selection
	/**
	 * @var Selection
	 */
	public $selection;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $object     object|Parameters object or Parameters with a main object
	 * @param $form       array
	 */
	public function __construct($class_name, $object, array $form)
	{
		$this->class_name = $class_name;
		$this->selection  = new Selection($object, $form);
	}

	//---------------------------------------------------------------------------------------- export
	/**
	 * @return string
	 */
	public function export()
	{
		$data       = $this->selection->readDataSelect(null, null, new Translate());
		$properties = $data->getProperties();

		// create temporary file
		$application   = Session::current()->get(Application::class);
		$tmp           = $application->getTemporaryFilesPath();
		$short_class   = Names::classToProperty($this->class_name);
		$csv_file_name = tempnam($tmp, $short_class . '_') . '.csv';
		$file          = fopen($csv_file_name, 'w');

		// write first line (properties path)
		$row = [];
		foreach ($this->selection->getListSettings()->properties as $property) {
			if (isset($properties[$property->path])) {
				$row[] = $property->title();
			}
		}
		fputcsv($file, $row);

		// format dates
		foreach ($properties as $property_path => $property) {
			if ($property instanceof Reflection_Property) {
				if ($property->getType()->isDateTime()) {
					$date_times[$property_path] = true;
				}
				if ($property->getListAnnotation('values')->values()) {
					$translate[$property_path] = true;
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
						$value = substr($value, 8, 2) . SL . substr($value, 5, 2) . SL . substr($value, 0, 4);
					}
					else {
						$value = substr($value, 8, 2) . SL . substr($value, 5, 2) . SL . substr($value, 0, 4)
						. SP . substr($value, 11, 2) . ':' . substr($value, 14, 2) . ':' . substr($value, 17, 2);
					}
				}
				elseif (isset($translate[$property_path])) {
					$value = Loc::tr($value);
				}
				$write[] = str_replace(DQ, Q . Q, $value);
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
