<?php
namespace SAF\Framework;

/*
use PHPExcel;
use PHPExcel_IOFactory;

require dirname(__FILE__) . '/../../vendor/PHPExcel/Classes/PHPExcel.php';
*/

/**
 * Excel file
 *
 * Needs PHPOffice/PHPExcel vendor project to be installed into your framework/vendor/ folder,
 * following this install procedure (Debian/Ubuntu example) :
 * apt-get install gnumeric
 * apt-get install php_zip
 * apt-get install php_xml
 * apt-get install php_gd2
 * cd framework/vendor
 * git pull https://github.com/PHPOffice/PHPExcel.git
 */
class Spreadsheet_File
{

	//-------------------------------------------------------------------------------- createFromFile
	/**
	 * @param $file_name string The Excel file name to be read
	 * @return Spreadsheet_File
	 */
	public static function createFromFile($file_name)
	{
		$source_object = PHPExcel_IOFactory::load($file_name);
		$destination_object = new Spreadsheet_File();
		$source_class = new Reflection_Class(get_class($source_object));
		$destination_class = new Reflection_Class(__CLASS__);
		$destination_properties = $destination_class->accessProperties();
		foreach ($source_class->accessProperties() as $source_property) {
			if (!$source_property->isStatic()) {
				$destination_property = $destination_properties[$source_property->name];
				$destination_property->setValue(
					$destination_object,
					$source_property->getValue($source_object)
				);
			}
		}
		return $destination_object;
	}

	//----------------------------------------------------------------------------------- fileToArray
	/**
	 * This is a direct, fast and optimized feature to read an excel file and return it's workseets
	 * into a simple PHP array, as fastest as possible, using gnumeric.
	 *
	 * This enable you to import huge xls files of 10MB and more
	 *
	 * @param $file_name string
	 * @param $errors    string[]
	 * @return array three dimensions (worksheet, row, column) array of read data
	 */
	public static function fileToArray($file_name, &$errors = [])
	{
		$csv_file = Application::current()->getTemporaryFilesPath() . SL . uniqid() . '.csv';
		exec('ssconvert ' . DQ . $file_name . DQ . SP . DQ . $csv_file . DQ . ' -S 2>&1 &');
		$count = 0;
		$result = [];
		while (is_file($csv_file . DOT . $count)) {
			$result[$csv_file . DOT . $count] = self::readCsvFile($csv_file . DOT . $count, $errors);
			$count ++;
		}
		return $result;
	}

	//----------------------------------------------------------------------------------- readCsvFile
	/**
	 * @param $csv_file string
	 * @param $errors   string[]
	 * @return array
	 */
	public static function readCsvFile($csv_file, &$errors = [])
	{
		$lines = [];
		$row   = 0;
		$f = fopen($csv_file, 'r');
		if ($f) while ($buf = fgetcsv($f)) {
			$row ++;
			if (($column = array_search('#REF!', $buf)) !== false) {
				$column ++;
				$errors[] = str_replace(
					['$1', '$2'],
					[$row, $column],
					Loc::tr('unsolved reference at row $1 and column $2')
				);
			}
			$lines[] = $buf;
		}
		fclose($f);
		return $lines;
	}

}
